<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\PlantillaEvaluacionData;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Services\PlantillaEvaluacionService;

class ActualizarPlantillaEvaluacionAction
{
    public function __construct(private readonly PlantillaEvaluacionService $service) {}

    public function execute(PlantillaEvaluacion $plantilla, PlantillaEvaluacionData $data)
    {
        return $this->service->update($plantilla, $data);
    }
}
