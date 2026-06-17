<?php

namespace App\Listeners\Seguridad;

use App\Domains\Seguridad\Services\BitacoraService;
use Illuminate\Auth\Events\Lockout;

class RegistrarBloqueoLogin
{
    public function __construct(private readonly BitacoraService $bitacora)
    {
    }

    public function handle(Lockout $event): void
    {
        $email = $event->request->string('email')->toString() ?: null;

        $this->bitacora->registrar([
            'correo_usuario' => $email,
            'accion' => 'login_bloqueado',
            'modulo' => 'Seguridad y Accesos',
            'entidad' => 'autenticacion',
            'entidad_id' => $email,
            'descripcion' => 'Bloqueo temporal por demasiados intentos de inicio de sesión.',
            'severidad' => 'seguridad',
            'valores_nuevos' => [
                'correo_intentado' => $email,
                'ip' => $event->request->ip(),
                'motivo' => 'Demasiados intentos de inicio de sesión',
            ],
        ]);
    }
}
