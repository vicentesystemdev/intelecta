<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Services\PreguntaService;

class CrearPreguntaAction
{
    public function __construct(private readonly PreguntaService $service) {}

    public function execute(PreguntaData $data)
    {
        return $this->service->create($data);
    }
}
