<?php

namespace App\Domains\Institucional\Actions;

use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VincularUsuarioPersonalAction
{
    public function __construct(private readonly BitacoraService $bitacora) {}

    public function execute(int $userId, int $personalId, User $actor, string $motivo): PersonalInstitucional
    {
        Validator::make(['motivo' => $motivo], ['motivo' => ['required', 'string', 'min:10', 'max:500']])->validate();
        try {
            return DB::transaction(function () use ($userId, $personalId, $actor, $motivo): PersonalInstitucional {
                // Same account mutex as Block 2: actor revocation cannot race identity confirmation.
                if (DB::getDriverName() === 'pgsql') {
                    DB::select('SELECT pg_advisory_xact_lock(?)', [CuentaService::LOCK_KEY]);
                }
                if (! $actor->fresh()?->canChangeLoginEmail()) {
                    throw new AuthorizationException('Solo TI puede confirmar un vínculo de identidad.');
                }
                $user = User::lockForUpdate()->find($userId);
                $personal = PersonalInstitucional::lockForUpdate()->find($personalId);
                if (! $user || ! $personal) {
                    throw ValidationException::withMessages(['user_id' => 'Comprueba los IDs de la cuenta y del personal.']);
                }
                if ($personal->user_id !== null || $user->personalInstitucional()->exists()) {
                    throw ValidationException::withMessages(['user_id' => 'La cuenta o el personal ya tiene un vínculo. No se permite reemplazarlo.']);
                }
                $personal->user()->associate($user);
                $personal->save();
                $this->bitacora->registrar([
                    'user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
                    'rol_usuario' => 'Super Administrador', 'modulo' => 'Organización institucional',
                    'accion' => 'vincular_usuario_personal', 'entidad' => 'personal_institucional', 'entidad_id' => $personalId,
                    'descripcion' => 'Vínculo explícito confirmado por TI: '.$motivo,
                    'valores_anteriores' => ['user_id' => null], 'valores_nuevos' => ['user_id' => $userId], 'severidad' => 'seguridad',
                ]);

                return $personal;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['user_id' => 'Otra operación vinculó esta cuenta. No se realizó ningún reemplazo.']);
        }
    }
}
