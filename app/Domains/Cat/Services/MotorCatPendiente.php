<?php

namespace App\Domains\Cat\Services;

use App\Domains\Cat\Contracts\MotorCat;
use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\DTOs\DecisionCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;

class MotorCatPendiente implements MotorCat
{
    public function calcular(ContextoCat $contexto): DecisionCat
    {
        throw new MotorCatNoDisponible('Adaptador real pendiente del contrato definitivo.');
    }
}
