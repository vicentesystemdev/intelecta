<?php

namespace App\Domains\Resultados\Services;

use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Repositories\ResultadoAcademicoRepository;

class ResultadoAcademicoService
{
    public function __construct(
        private readonly ResultadoAcademicoRepository $repository,
    ) {}

    public function recalculate(EvaluacionAplicada $evaluacion): EvaluacionAplicada
    {
        $evaluacion->load('respuestas');

        $puntaje = round((float) $evaluacion->respuestas->sum('puntaje_obtenido_resp'), 2);
        $maximo = round((float) $evaluacion->respuestas->sum('puntaje_maximo_resp'), 2);
        $porcentaje = $maximo > 0 ? round(($puntaje / $maximo) * 100, 2) : 0;

        $evaluacion->update([
            'puntaje_total_eval_apl' => $puntaje,
            'puntaje_maximo_eval_apl' => $maximo,
            'porcentaje_eval_apl' => min(100, $porcentaje),
        ]);

        return $evaluacion->refresh();
    }

    public function updatePerformance(EvaluacionAplicada $evaluacion): void
    {
        $this->repository->updatePerformance($evaluacion->postulante);
    }
}
