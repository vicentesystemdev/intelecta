<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\PlantillaEvaluacionData;
use App\Domains\Evaluaciones\Services\PlantillaEvaluacionService;

class CrearPlantillaEvaluacionAction
{
    public function __construct(private readonly PlantillaEvaluacionService $service) {}

    public function execute(PlantillaEvaluacionData $data)
    {
        return $this->service->create($data);
    }
}
