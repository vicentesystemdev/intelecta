<?php

namespace App\Domains\Academico\Enums;

enum NivelSeguimiento: string
{
    case AltoRendimiento = 'Alto rendimiento';
    case SeguimientoRegular = 'Seguimiento regular';
    case AtencionPrioritaria = 'Atención prioritaria';

    public static function desdePromedio(float $promedio): self
    {
        return match (true) {
            $promedio >= 80 => self::AltoRendimiento,
            $promedio >= 65 => self::SeguimientoRegular,
            default => self::AtencionPrioritaria,
        };
    }
}
