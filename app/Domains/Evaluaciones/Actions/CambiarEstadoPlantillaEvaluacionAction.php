<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Services\PlantillaEvaluacionService;

class CambiarEstadoPlantillaEvaluacionAction
{
    public function __construct(private readonly PlantillaEvaluacionService $service) {}

    public function execute(PlantillaEvaluacion $plantilla)
    {
        return $this->service->changeStatus($plantilla);
    }
}
