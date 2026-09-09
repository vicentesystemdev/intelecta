<?php

namespace App\Domains\Seguridad\Services;

use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CuentaService
{
    // One transaction-scoped PostgreSQL mutex for every account-security mutation.
    public const LOCK_KEY = 720260902;

    public function __construct(private readonly RevocarAccesoService $revoker, private readonly BitacoraService $bitacora) {}

    public function transaction(Closure $operation): mixed
    {
        return DB::transaction(function () use ($operation) {
            if (DB::getDriverName() === 'pgsql') {
                DB::select('SELECT pg_advisory_xact_lock(?)', [self::LOCK_KEY]);
            }

            return $operation();
        }, 3);
    }

    private function authorize(User $actor): User
    {
        $actor = User::query()->findOrFail($actor->id);
        if (! $actor->hasRole('Super Administrador') || ! $actor->cuentaActiva()) {
            throw new AuthorizationException('Solo un Super Administrador activo puede administrar identidades digitales.');
        }

        return $actor;
    }

    private function protectLast(User $user, EstadoCuenta $state, array $roles, User $actor): void
    {
        if ($user->hasRole('Super Administrador') && $user->cuentaActiva()
            && ($state !== EstadoCuenta::ACTIVA || ! in_array('Super Administrador', $roles, true))
            && ! User::role('Super Administrador')->where('estado_cuenta', EstadoCuenta::ACTIVA->value)
                ->whereNotNull('email_verified_at')->whereKeyNot($user->id)->exists()) {
            // Record after the rejected transaction has rolled back (see administrative()).
            throw ValidationException::withMessages(['ultimo_sa' => 'Debe permanecer al menos un Super Administrador activo. Activa y verifica otra cuenta SA antes de continuar.']);
        }
    }

    private function administrative(User $actor, int $targetId, Closure $operation): mixed
    {
        try {
            return $this->transaction(function () use ($actor, $targetId, $operation) {
                $actor = $this->authorize($actor);
                $user = User::query()->lockForUpdate()->findOrFail($targetId);

                return $operation($user, $actor);
            });
        } catch (ValidationException $exception) {
            if (isset($exception->errors()['ultimo_sa'])) {
                $this->audit('rechazar_ultimo_sa', $actor, $targetId);
            }
            throw $exception;
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'El correo de acceso ya pertenece a otra cuenta.']);
        }
    }

    public function create(User $actor, array $data): User
    {
        try {
            $user = $this->transaction(function () use ($actor, $data): User {
                $actor = $this->authorize($actor);
                $user = User::create([
                    'name' => $data['name'], 'email' => $data['email'],
                    'password' => Str::random(64), // Hashed by User; never returned or logged.
                ]);
                $user->forceFill(['estado_cuenta' => EstadoCuenta::PENDIENTE, 'email_verified_at' => null])->save();
                $user->syncRoles([$data['role']]);
                $this->audit('crear', $actor, $user->id, ['estado' => EstadoCuenta::PENDIENTE->value, 'rol' => $data['role']]);

                return $user;
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'El correo de acceso ya pertenece a otra cuenta.']);
        }
        $this->sendAccess($actor, $user->id);

        return $user;
    }

    public function update(User $actor, int $id, array $data): User
    {
        return $this->administrative($actor, $id, function (User $user, User $actor) use ($data): User {
            $emailChanged = $user->email !== $data['email'];
            $roles = $user->getRoleNames()->all();
            $roleChanged = $roles !== [$data['role']];
            $state = $emailChanged && $user->estado_cuenta !== EstadoCuenta::BLOQUEADA
                ? EstadoCuenta::PENDIENTE : $user->estado_cuenta;
            $this->protectLast($user, $state, [$data['role']], $actor);
            $before = ['email' => $user->email, 'roles' => $roles, 'estado' => $user->estado_cuenta->value];
            if ($emailChanged || $roleChanged) {
                $this->revoker->execute($user);
            }
            $user->name = $data['name'];
            if ($emailChanged) {
                $user->forceFill(['email' => $data['email'], 'email_verified_at' => null, 'estado_cuenta' => $state]);
                Password::broker()->deleteToken($user); // Also discard any token under the new address.
            }
            $user->save();
            if ($roleChanged) {
                $user->syncRoles([$data['role']]);
                $this->audit('cambiar_rol', $actor, $user->id, ['roles' => [$data['role']]], ['roles' => $roles]);
            }
            $this->audit($emailChanged ? 'cambiar_correo_acceso' : 'editar', $actor, $user->id,
                ['email' => $user->email, 'roles' => [$data['role']], 'estado' => $state->value], $before);

            return $user;
        });
    }

    public function block(User $actor, int $id, string $reason): void
    {
        $this->administrative($actor, $id, function (User $user, User $actor) use ($reason): void {
            $this->protectLast($user, EstadoCuenta::BLOQUEADA, $user->getRoleNames()->all(), $actor);
            $before = $user->estado_cuenta->value;
            $user->estado_cuenta = EstadoCuenta::BLOQUEADA;
            $this->revoker->execute($user);
            $this->audit('bloquear_cuenta', $actor, $user->id, ['estado' => 'bloqueada', 'motivo' => $reason], ['estado' => $before]);
        });
    }

    public function unblock(User $actor, int $id): void
    {
        $this->administrative($actor, $id, function (User $user, User $actor): void {
            if ($user->estado_cuenta !== EstadoCuenta::BLOQUEADA) {
                throw ValidationException::withMessages(['estado' => 'La cuenta no está bloqueada.']);
            }
            $user->estado_cuenta = $user->hasVerifiedEmail() ? EstadoCuenta::ACTIVA : EstadoCuenta::PENDIENTE;
            $this->revoker->execute($user);
            $this->audit('desbloquear_cuenta', $actor, $user->id, ['estado' => $user->estado_cuenta->value]);
        });
    }

    public function sendAccess(User $actor, int $id): void
    {
        [$user, $actor, $token] = $this->administrative($actor, $id, function (User $user, User $actor): array {
            if ($user->estado_cuenta !== EstadoCuenta::PENDIENTE) {
                throw ValidationException::withMessages(['activacion' => 'El reenvío de activación solo corresponde a cuentas pendientes.']);
            }
            // Native hashed, expiring, single-use token. Explicit resend replaces the previous token.
            $token = Password::broker()->createToken($user);
            $this->audit('solicitar_activacion', $actor, $user->id);

            return [$user, $actor, $token];
        });
        // Never hold the account-security mutex while waiting on a mail transport.
        // A concurrent block/email change can invalidate this token, never restore access.
        try {
            $user->sendPasswordResetNotification($token);
        } catch (Throwable $exception) {
            $this->audit('fallo_envio_activacion', $actor, $user->id);
            // No exception text: transport failures may embed credentials or message tokens.
            throw ValidationException::withMessages(['activacion' => 'No se pudo enviar el acceso. La cuenta permanece pendiente; revisa el correo configurado y reintenta.']);
        }
    }

    public function verify(User $original): bool
    {
        return $this->transaction(function () use ($original): bool {
            $user = User::query()->lockForUpdate()->findOrFail($original->id);
            if (! hash_equals($user->email, $original->email)) {
                throw new AuthorizationException('El enlace no corresponde al correo actual.');
            }
            if ($user->hasVerifiedEmail()) {
                return false;
            }
            $user->email_verified_at = now();
            if ($user->estado_cuenta === EstadoCuenta::PENDIENTE) {
                $user->estado_cuenta = EstadoCuenta::ACTIVA;
            }
            $user->save();
            $this->audit('verificar_correo', $user, $user->id, ['estado' => $user->estado_cuenta->value]);
            if ($user->estado_cuenta === EstadoCuenta::ACTIVA) {
                $this->audit('activar_cuenta', $user, $user->id);
            }
            $original->refresh();

            return true;
        });
    }

    public function resetPassword(array $credentials): string
    {
        // Serialize token validation AND consumption with block/email-change/resend operations.
        return $this->transaction(fn () => Password::broker()->reset($credentials, function (User $user, string $password): void {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $user->password = $password;
            $this->revoker->execute($user, resetTokens: false);
            event(new PasswordReset($user));
        }));
    }

    public function changeOwnPassword(User $original, string $password): User
    {
        return $this->transaction(function () use ($original, $password): User {
            $user = User::query()->lockForUpdate()->findOrFail($original->id);
            if ($user->estado_cuenta === EstadoCuenta::BLOQUEADA || $user->version_acceso !== $original->version_acceso) {
                throw new AuthorizationException('La sesión ya no autoriza este cambio.');
            }
            $user->password = $password;
            $this->revoker->execute($user);
            $this->audit('cambiar_password', $user, $user->id);

            return $user;
        });
    }

    private function audit(string $action, User $actor, int $id, array $after = [], array $before = []): void
    {
        $this->bitacora->registrar([
            'user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
            'rol_usuario' => $actor->getRoleNames()->first(), 'accion' => $action, 'modulo' => 'Usuarios',
            'entidad' => 'users', 'entidad_id' => $id, 'descripcion' => 'Operación de ciclo de vida de cuenta: '.$action,
            'valores_anteriores' => $before ?: null, 'valores_nuevos' => $after ?: null, 'severidad' => 'seguridad',
        ]);
    }
}
