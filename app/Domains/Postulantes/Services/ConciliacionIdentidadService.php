<?php

namespace App\Domains\Postulantes\Services;

use App\Domains\Postulantes\Actions\VincularUsuarioPostulanteAction;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ConciliacionIdentidadService
{
    public function destino(): array
    {
        $connection = DB::connection();

        return collect($connection->getConfig())->only(['driver', 'host', 'port', 'database'])->all();
    }

    public function huella(User|Postulante $model): string
    {
        // El vínculo y su timestamp pueden cambiar en una reejecución legítima.
        // No se exportan contraseñas ni otros atributos: solo su huella opaca.
        $attributes = $model->getAttributes();
        if ($model instanceof Postulante) {
            unset($attributes['user_id'], $attributes['updated_at']);
        }
        ksort($attributes);

        return hash('sha256', json_encode($attributes, JSON_THROW_ON_ERROR));
    }

    /** No se buscan parejas aquí. Cada ID procede de un manifiesto revisado. */
    public function ejecutar(array $manifest, bool $apply = false, ?User $actor = null, string $motivo = ''): array
    {
        Validator::make($manifest, [
            'destino' => ['required', 'array'],
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.user_id' => ['required', 'integer', 'min:1', 'distinct'],
            'filas.*.id_post' => ['required', 'integer', 'min:1', 'distinct'],
            'filas.*.clasificacion' => ['required', 'in:A'],
            'filas.*.evidencia' => ['required', 'string', 'min:20'],
            'filas.*.huella_usuario' => ['required', 'regex:/^[a-f0-9]{64}$/'],
            'filas.*.huella_expediente' => ['required', 'regex:/^[a-f0-9]{64}$/'],
        ])->validate();
        if ($manifest['destino'] !== $this->destino()) {
            throw ValidationException::withMessages(['destino' => 'El manifiesto no corresponde a esta conexión.']);
        }
        if ($apply) {
            if (! $actor?->canChangeLoginEmail()) {
                throw new AuthorizationException('Se requiere un actor SA activo y verificado explícitamente autorizado.');
            }
            Validator::make(['motivo' => $motivo], ['motivo' => ['required', 'string', 'min:10', 'max:500']])->validate();
            foreach ($manifest['filas'] as $row) {
                if (($row['revision'] ?? null) !== 'aprobado') {
                    throw ValidationException::withMessages(['revision' => 'Todas las filas deben estar aprobadas expresamente.']);
                }
            }
        }

        $operation = function () use ($manifest, $apply, $actor, $motivo): array {
            $result = [];
            foreach ($manifest['filas'] as $row) {
                $users = User::query();
                $profiles = Postulante::withTrashed();
                if ($apply) {
                    $users->lockForUpdate();
                    $profiles->lockForUpdate();
                }
                $user = $users->find($row['user_id']);
                $post = $profiles->find($row['id_post']);
                if (! $user || ! $post || $post->trashed()
                    || ! $user->hasRole('Estudiante') || $user->getRoleNames()->diff(['Estudiante'])->isNotEmpty()
                    || ! hash_equals($row['huella_usuario'], $this->huella($user))
                    || ! hash_equals($row['huella_expediente'], $this->huella($post))) {
                    throw ValidationException::withMessages(['fila' => 'Identidad, estado, roles o huellas cambiaron; repetir la revisión.']);
                }
                $other = $user->postulante()->withTrashed()->where('id_post', '!=', $post->id_post)->exists();
                if ($other || ($post->user_id !== null && $post->user_id !== $user->id)) {
                    throw ValidationException::withMessages(['vinculo' => 'Conflicto: nunca se reemplaza un vínculo existente.']);
                }
                $alreadyLinked = $post->user_id === $user->id;
                if ($apply && ! $alreadyLinked) {
                    $before = DB::table('bitacora_sistema')->max('id_bitacora');
                    app(VincularUsuarioPostulanteAction::class)->execute($user->id, $post->id_post, $actor, $motivo);
                    // El registrador general es best-effort; este lote exige auditoría durable.
                    $audit = DB::table('bitacora_sistema')->where('id_bitacora', '>', $before ?? 0)
                        ->where('accion', 'vincular_identidad')->where('user_id', $actor->id)
                        ->where('entidad_id', (string) $post->id_post)->exists();
                    if (! $audit) {
                        throw new RuntimeException('No se confirmó la bitácora: rollback íntegro del lote.');
                    }
                }
                $result[] = ['user_id' => $user->id, 'id_post' => $post->id_post,
                    'resultado' => $alreadyLinked ? 'ya_vinculado_sin_cambios' : ($apply ? 'vinculado' : 'propuesto_sin_escritura')];
            }

            return $result;
        };

        // Misma coordinación global y misma acción auditada que la vinculación individual.
        return $apply ? app(CuentaService::class)->transaction($operation) : $operation();
    }
}
