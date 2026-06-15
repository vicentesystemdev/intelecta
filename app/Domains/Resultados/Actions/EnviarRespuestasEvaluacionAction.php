<?php

namespace App\Domains\Resultados\Actions;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\DTOs\EnviarEvaluacionData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Services\EvaluacionAplicadaService;

class EnviarRespuestasEvaluacionAction
{
    public function __construct(
        private readonly EvaluacionAplicadaService $service,
    ) {}

    public function execute(
        EvaluacionAplicada $evaluacion,
        Postulante $postulante,
        EnviarEvaluacionData $data,
    ): EvaluacionAplicada {
        return $this->service->enviar($evaluacion, $postulante, $data);
    }
}
