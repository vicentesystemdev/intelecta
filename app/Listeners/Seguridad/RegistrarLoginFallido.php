<?php

namespace App\Listeners\Seguridad;

use App\Domains\Seguridad\Services\BitacoraService;
use Illuminate\Auth\Events\Failed;

class RegistrarLoginFallido
{
    public function __construct(private readonly BitacoraService $bitacora)
    {
    }

    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        $this->bitacora->registrar([
            'correo_usuario' => $email,
            'accion' => 'login_fallido',
            'modulo' => 'Seguridad y Accesos',
            'entidad' => 'autenticacion',
            'entidad_id' => $email,
            'descripcion' => 'Intento fallido de inicio de sesión.',
            'severidad' => 'aviso',
            'valores_nuevos' => [
                'correo_intentado' => $email,
                'motivo' => 'Credenciales no válidas',
                'guard' => $event->guard,
            ],
        ]);
    }
}
