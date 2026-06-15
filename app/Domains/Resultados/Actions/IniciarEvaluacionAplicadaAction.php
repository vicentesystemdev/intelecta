<?php

namespace App\Domains\Resultados\Actions;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Services\EvaluacionAplicadaService;

class IniciarEvaluacionAplicadaAction
{
    public function __construct(
        private readonly EvaluacionAplicadaService $service,
    ) {}

    public function execute(
        Postulante $postulante,
        PlantillaEvaluacion $plantilla,
        ?int $simulacroId = null,
        ?string $tipo = null,
    ): EvaluacionAplicada {
        return $this->service->iniciar($postulante, $plantilla, $simulacroId, $tipo);
    }
}
