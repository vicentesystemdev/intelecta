<?php

namespace App\Domains\Academico\Enums;

enum EstadoSimulacro: string
{
    case Programado = 'programado';
    case EnPreparacion = 'en preparación';
    case Aplicado = 'aplicado';
    case Cerrado = 'cerrado';
}
