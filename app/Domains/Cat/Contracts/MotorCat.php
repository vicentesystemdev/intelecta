<?php

namespace App\Domains\Cat\Contracts;

use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\DTOs\DecisionCat;

interface MotorCat
{
    // Puerto interno PHP, independiente del futuro contrato HTTP.
    public function calcular(ContextoCat $contexto): DecisionCat;
}
