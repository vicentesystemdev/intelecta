<?php

namespace App\Listeners\Seguridad;

use App\Domains\Seguridad\Services\BitacoraService;
use Illuminate\Auth\Events\Login;

class RegistrarLoginExitoso
{
    public function __construct(private readonly BitacoraService $bitacora)
    {
    }

    public function handle(Login $event): void
    {
        $user = $event->user;
        $role = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->first()
            : null;

        $this->bitacora->registrar([
            'user_id' => $user->getAuthIdentifier(),
            'nombre_usuario' => $user->name ?? null,
            'correo_usuario' => $user->email ?? null,
            'rol_usuario' => $role,
            'accion' => 'login_exitoso',
            'modulo' => 'Seguridad y Accesos',
            'entidad' => 'users',
            'entidad_id' => $user->getAuthIdentifier(),
            'descripcion' => 'Inicio de sesión exitoso de usuario institucional.',
            'severidad' => 'seguridad',
            'valores_nuevos' => [
                'correo' => $user->email ?? null,
                'nombre' => $user->name ?? null,
                'rol' => $role,
                'guard' => $event->guard,
            ],
        ]);
    }
}
