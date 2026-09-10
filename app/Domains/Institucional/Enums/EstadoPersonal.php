<?php

namespace App\Domains\Institucional\Enums;

enum EstadoPersonal: string
{
    case PENDIENTE = 'pendiente';
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
}
