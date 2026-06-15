<?php

namespace App\Domains\Resultados\Actions;

use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Services\ResultadoAcademicoService;

class RecalcularResultadoEvaluacionAction
{
    public function __construct(
        private readonly ResultadoAcademicoService $service,
    ) {}

    public function execute(EvaluacionAplicada $evaluacion): EvaluacionAplicada
    {
        return $this->service->recalculate($evaluacion);
    }
}
