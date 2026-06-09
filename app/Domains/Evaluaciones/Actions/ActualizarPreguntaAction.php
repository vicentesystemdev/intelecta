<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Services\PreguntaService;

class ActualizarPreguntaAction
{
    public function __construct(private readonly PreguntaService $service) {}

    public function execute(Pregunta $pregunta, PreguntaData $data)
    {
        return $this->service->update($pregunta, $data);
    }
}
