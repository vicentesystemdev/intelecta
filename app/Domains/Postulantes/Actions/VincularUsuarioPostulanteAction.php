<?php

namespace App\Domains\Postulantes\Actions;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VincularUsuarioPostulanteAction
{
    public function __construct(private readonly BitacoraService $bitacora) {}

    public function execute(int $userId, int $postulanteId, User $actor, string $motivo): Postulante
    {
        if (! $actor->canChangeLoginEmail()) {
            throw new AuthorizationException('Solo TI puede confirmar un vínculo de identidad.');
        }
        $motivo = trim($motivo);
        Validator::make(['motivo' => $motivo], ['motivo' => ['required', 'string', 'min:10', 'max:500']])->validate();

        try {
            return app(CuentaService::class)->transaction(function () use ($userId, $postulanteId, $actor, $motivo): Postulante {
                $actor = User::findOrFail($actor->id);
                if (! $actor->canChangeLoginEmail()) {
                    throw new AuthorizationException('Solo un SA activo puede confirmar el vínculo.');
                }
                // Consistent lock order with account deletion; UNIQUE is the final concurrent guard.
                $user = User::query()->lockForUpdate()->find($userId);
                $postulante = Postulante::withTrashed()->lockForUpdate()->find($postulanteId);

                if (! $user || $user->getRoleNames()->diff(['Estudiante'])->isNotEmpty()) {
                    throw ValidationException::withMessages(['user_id' => 'Selecciona una cuenta sin roles o exclusivamente Estudiante.']);
                }
                if (! $postulante || $postulante->trashed()) {
                    throw ValidationException::withMessages(['id_post' => 'Selecciona un expediente existente no archivado.']);
                }
                if ($postulante->user_id !== null || $user->postulante()->withTrashed()->exists()) {
                    throw ValidationException::withMessages(['vinculo' => 'La cuenta o el expediente ya tiene un vínculo. No se permite reemplazarlo, incluso si está archivado.']);
                }

                $postulante->user()->associate($user);
                $postulante->save();
                $this->bitacora->registrar([
                    'user_id' => $actor->id,
                    'nombre_usuario' => $actor->name,
                    'correo_usuario' => $actor->email,
                    'rol_usuario' => 'Super Administrador',
                    'accion' => 'vincular_identidad',
                    'modulo' => 'Postulantes',
                    'entidad' => 'postulantes',
                    'entidad_id' => $postulante->id_post,
                    'descripcion' => 'Vínculo explícito confirmado por TI: '.$motivo,
                    'valores_anteriores' => ['user_id' => null],
                    'valores_nuevos' => ['user_id' => $user->id, 'id_post' => $postulante->id_post],
                    'severidad' => 'seguridad',
                ]);

                return $postulante;
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['vinculo' => 'Otra operación confirmó un vínculo para esta cuenta. Revisa los IDs; no se realizó ningún reemplazo.']);
        }
    }
}
