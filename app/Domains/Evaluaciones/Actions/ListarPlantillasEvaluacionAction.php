<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\Services\PlantillaEvaluacionService;

class ListarPlantillasEvaluacionAction
{
    public function __construct(private readonly PlantillaEvaluacionService $service) {}

    public function execute(array $filters)
    {
        return $this->service->list($filters);
    }
}
