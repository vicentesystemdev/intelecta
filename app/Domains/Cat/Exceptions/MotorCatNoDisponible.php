<?php

namespace App\Domains\Cat\Exceptions;

use RuntimeException;

class MotorCatNoDisponible extends RuntimeException
{
    public function __construct(string $message = 'Motor CAT no disponible.', public readonly string $codigo = 'no_disponible')
    {
        parent::__construct($message);
    }
}
