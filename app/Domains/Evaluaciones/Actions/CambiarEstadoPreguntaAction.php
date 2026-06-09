<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Services\PreguntaService;

class CambiarEstadoPreguntaAction
{
    public function __construct(private readonly PreguntaService $service) {}

    public function execute(Pregunta $pregunta)
    {
        return $this->service->changeStatus($pregunta);
    }
}
