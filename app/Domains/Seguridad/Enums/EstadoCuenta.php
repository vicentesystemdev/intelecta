<?php

namespace App\Domains\Seguridad\Enums;

enum EstadoCuenta: string
{
    case PENDIENTE = 'pendiente';
    case ACTIVA = 'activa';
    case BLOQUEADA = 'bloqueada';
}
