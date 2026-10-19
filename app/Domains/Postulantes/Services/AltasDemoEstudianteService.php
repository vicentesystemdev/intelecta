<?php

namespace App\Domains\Postulantes\Services;

use App\Domains\Postulantes\Actions\VincularUsuarioPostulanteAction;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use App\Support\Validation\InputRules;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class AltasDemoEstudianteService
{
    public function __construct(private readonly ConciliacionIdentidadService $identity, private readonly CuentaService $accounts, private readonly BitacoraService $audit) {}

    public function propuesta(): array
    {
        $report = app(AuditoriaIdentidadService::class)->generar();
        $rows = collect($report['filas'])->where('clasificacion', 'D')->map(function ($row): array {
            if (! $row['coincide_fixture']) {
                throw ValidationException::withMessages(['origen' => 'Un expediente sin cuenta no coincide con el catálogo DEMO; revisar manualmente.']);
            }

            return ['id_post' => $row['id_post'], 'nombre' => $row['nombre'], 'correo' => $row['correo'],
                'documento' => $row['documento'], 'huella_expediente' => $row['huella_expediente'],
                'matriculas' => $row['matriculas'], 'estado_postulante' => $row['estado_postulante'],
                'evaluacion_permitida' => $row['evaluacion_administrativa'], 'revision' => 'pendiente'];
        })->values()->all();

        return ['tipo' => 'altas_demo_estudiantes', 'demo' => true, 'lote_id' => (string) Str::uuid(),
            'destino' => $report['destino'], 'fuentes_sha256' => $report['fuentes_sha256'],
            'politica' => 'Solo Estudiante; activación administrativa DEMO; contraseña inicial desconocida; token nativo de establecimiento de contraseña, caducable y de un solo uso; sin correo ni secretos en logs.',
            'caducidad_minutos' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire'), 'filas' => $rows];
    }

    public function ejecutar(array $manifest, bool $apply = false, ?User $actor = null, string $motivo = '', ?string $deliveryPath = null): array
    {
        if (! app()->environment('local', 'testing')) {
            throw new AuthorizationException('La regularización DEMO solo está disponible en local/testing.');
        }
        Validator::make($manifest, [
            'tipo' => ['required', 'in:altas_demo_estudiantes'], 'demo' => ['required', 'accepted'], 'lote_id' => ['required', 'uuid'],
            'filas' => ['required', 'array', 'min:1'], 'filas.*.id_post' => ['required', 'integer', 'min:1', 'distinct'],
            'filas.*.nombre' => ['required', 'string', 'max:255'], 'filas.*.correo' => ['required', ...InputRules::email(), 'distinct:ignore_case'],
            'filas.*.documento' => ['required', 'string'], 'filas.*.huella_expediente' => ['required', 'regex:/^[a-f0-9]{64}$/'],
        ])->validate();
        if (($manifest['destino'] ?? null) !== $this->identity->destino()) {
            throw ValidationException::withMessages(['destino' => 'El manifiesto no corresponde a esta conexión.']);
        }
        if (! Role::where('name', 'Estudiante')->where('guard_name', 'web')->exists()) {
            throw ValidationException::withMessages(['rol' => 'Falta el rol Estudiante del catálogo auditado. No se crea automáticamente.']);
        }
        if ($apply) {
            $this->authorize($actor, $motivo);
            foreach ($manifest['filas'] as $row) {
                if (($row['revision'] ?? null) !== 'aprobado') {
                    throw ValidationException::withMessages(['revision' => 'Cada alta necesita aprobación explícita.']);
                }
            }
        }
        $written = false;
        $operation = function () use ($manifest, $apply, $actor, $motivo, $deliveryPath, &$written): array {
            $results = [];
            $credentials = [];
            foreach ($manifest['filas'] as $row) {
                $query = Postulante::withTrashed();
                $post = ($apply ? $query->lockForUpdate() : $query)->find($row['id_post']);
                if (! $post || $post->trashed() || ! hash_equals($row['huella_expediente'], $this->identity->huella($post))
                    || $post->nombres_post.' '.$post->apellidos_post !== $row['nombre']
                    || $post->email_post !== $row['correo'] || $post->ci_post !== $row['documento']) {
                    throw ValidationException::withMessages(['identidad' => 'Expediente inexistente, archivado o distinto del aprobado.']);
                }
                if (Postulante::withTrashed()->whereKeyNot($post->id_post)->whereRaw('LOWER(email_post) = ?', [mb_strtolower($row['correo'])])->exists()) {
                    throw ValidationException::withMessages(['correo' => 'Correo repetido en expedientes, incluidos archivados.']);
                }
                if ($post->user_id !== null) {
                    $user = $post->user;
                    if (! $user || $user->name !== $row['nombre'] || $user->email !== $row['correo']
                        || $user->getRoleNames()->all() !== ['Estudiante'] || $user->personalInstitucional()->exists()
                        || ! $this->marker($post, $manifest['lote_id'])) {
                        throw ValidationException::withMessages(['vinculo' => 'El expediente ya está vinculado fuera de este lote. Nunca se adopta ni sobrescribe.']);
                    }
                    $results[] = ['id_post' => $post->id_post, 'user_id' => $user->id, 'resultado' => 'ya_creada_sin_cambios'];

                    continue;
                }
                if (User::whereRaw('LOWER(email) = ?', [mb_strtolower($row['correo'])])->exists()) {
                    throw ValidationException::withMessages(['correo' => 'Existe una cuenta con ese correo: conciliación manual, no crear ni adoptar.']);
                }
                if (! $apply) {
                    $results[] = ['id_post' => $post->id_post, 'user_id' => null, 'resultado' => 'alta_propuesta_sin_escritura'];

                    continue;
                }
                $before = DB::table('bitacora_sistema')->max('id_bitacora') ?? 0;
                $user = $this->accounts->createDemo($actor, ['name' => $row['nombre'], 'email' => $row['correo']]);
                app(VincularUsuarioPostulanteAction::class)->execute($user->id, $post->id_post, $actor, $motivo);
                $this->accounts->assignRoles($actor, $user->id, ['roles' => ['Estudiante']]);
                $user->refresh();
                $token = Password::broker()->createToken($user);
                $this->audit->registrar(['user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
                    'rol_usuario' => $actor->rolesLabel(), 'accion' => 'alta_demo_estudiante', 'modulo' => 'Usuarios',
                    'entidad' => 'postulantes', 'entidad_id' => $post->id_post, 'descripcion' => $motivo, 'severidad' => 'seguridad',
                    'valores_nuevos' => ['user_id' => $user->id, 'id_post' => $post->id_post, 'lote_id' => $manifest['lote_id'], 'roles' => ['Estudiante'], 'acceso' => 'token_nativo_temporal']]);
                foreach (['crear_demo_controlado', 'vincular_identidad', 'asignar_roles', 'alta_demo_estudiante'] as $action) {
                    if (! DB::table('bitacora_sistema')->where('id_bitacora', '>', $before)->where('user_id', $actor->id)->where('accion', $action)
                        ->where('entidad_id', (string) ($action === 'vincular_identidad' || $action === 'alta_demo_estudiante' ? $post->id_post : $user->id))->exists()) {
                        throw new RuntimeException('Falta auditoría durable del alta; rollback íntegro.');
                    }
                }
                $credentials[] = $this->credential($user, $post, $token);
                $results[] = ['id_post' => $post->id_post, 'user_id' => $user->id, 'resultado' => 'creada_y_vinculada'];
            }
            if ($apply && $credentials !== []) {
                if (! $deliveryPath) {
                    throw new RuntimeException('Se necesita destino privado nuevo para el paquete cifrado.');
                }
                $this->writeEncrypted($deliveryPath, ['lote_id' => $manifest['lote_id'], 'destino' => $this->identity->destino(), 'cuentas' => $credentials]);
                $written = true;
            }

            return $results;
        };
        try {
            return $apply ? $this->accounts->transaction($operation) : $operation();
        } catch (Throwable $exception) {
            if ($written && $deliveryPath && is_file($deliveryPath)) {
                unlink($deliveryPath); // Solo el archivo nuevo creado por esta operación fallida.
            }
            throw $exception;
        }
    }

    public function authorize(?User $actor, string $motivo): void
    {
        if (! $actor || auth()->id() !== $actor->id || ! $actor->fresh()?->canChangeLoginEmail()) {
            throw new AuthorizationException('Se requiere contexto administrativo del SA autorizado, activo y verificado.');
        }
        Validator::make(['motivo' => $motivo], ['motivo' => ['required', 'string', 'min:10', 'max:500']])->validate();
    }

    public function marker(Postulante $post, ?string $batch = null): bool
    {
        return DB::table('bitacora_sistema')->where('accion', 'alta_demo_estudiante')->where('entidad', 'postulantes')
            ->where('entidad_id', (string) $post->id_post)->where('valores_nuevos->user_id', $post->user_id)
            ->when($batch, fn ($q) => $q->where('valores_nuevos->lote_id', $batch))->exists();
    }

    public function credential(User $user, Postulante $post, #[\SensitiveParameter] string $token): array
    {
        return ['id_post' => $post->id_post, 'user_id' => $user->id, 'correo' => $user->email, 'token' => $token,
            'ruta' => route('password.reset', ['token' => $token, 'email' => $user->email], false),
            'caduca' => now()->addMinutes((int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire'))->toIso8601String()];
    }

    public function privateNewPath(string $path): void
    {
        $root = realpath(storage_path('app/private'));
        $parent = realpath(dirname($path));
        $normalize = function ($value): string {
            $value = str_replace('\\', '/', $value);

            return PHP_OS_FAMILY === 'Windows' ? mb_strtolower($value) : $value;
        };
        if (! $root || ! $parent || ! str_starts_with($normalize($parent).'/', $normalize($root).'/') || file_exists($path)) {
            throw new RuntimeException('El archivo debe ser nuevo y estar dentro de storage/app/private; no se sobrescribe.');
        }
    }

    public function writeEncrypted(string $path, #[\SensitiveParameter] array $payload): void
    {
        $this->privateNewPath($path);
        $encrypted = Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR));
        $handle = fopen($path, 'x');
        if (! $handle) {
            throw new RuntimeException('No se pudo crear el paquete privado.');
        }
        $complete = false;
        try {
            if (fwrite($handle, $encrypted) !== strlen($encrypted)) {
                throw new RuntimeException('Escritura incompleta del paquete privado.');
            }
            $complete = true;
        } finally {
            fclose($handle);
            if (! $complete && is_file($path)) {
                unlink($path);
            }
        }
        @chmod($path, 0600);
    }
}
