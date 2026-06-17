<?php

namespace App\Listeners\Seguridad;

use App\Domains\Seguridad\Services\BitacoraService;
use Illuminate\Auth\Events\Logout;

class RegistrarLogout
{
    public function __construct(private readonly BitacoraService $bitacora)
    {
    }

    public function handle(Logout $event): void
    {
        $user = $event->user;
        $role = $user && method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->first()
            : null;

        $this->bitacora->registrar([
            'user_id' => $user?->getAuthIdentifier(),
            'nombre_usuario' => $user?->name,
            'correo_usuario' => $user?->email,
            'rol_usuario' => $role,
            'accion' => 'logout',
            'modulo' => 'Seguridad y Accesos',
            'entidad' => 'users',
            'entidad_id' => $user?->getAuthIdentifier(),
            'descripcion' => 'Cierre de sesión de usuario institucional.',
            'severidad' => 'info',
            'valores_nuevos' => [
                'correo' => $user?->email,
                'nombre' => $user?->name,
                'rol' => $role,
                'guard' => $event->guard,
            ],
        ]);
    }
}
