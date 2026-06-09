<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\Services\PreguntaService;

class ListarPreguntasAction
{
    public function __construct(private readonly PreguntaService $service) {}

    public function execute(array $filters)
    {
        return $this->service->list($filters);
    }
}
