<?php

namespace App\Domains\Resultados\Repositories;

use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Models\RespuestaEvaluacion;

class RespuestaEvaluacionRepository
{
    public function save(EvaluacionAplicada $evaluacion, array $attributes): RespuestaEvaluacion
    {
        return RespuestaEvaluacion::updateOrCreate(
            [
                'id_eval_apl' => $evaluacion->id_eval_apl,
                'id_preg' => $attributes['id_preg'],
            ],
            $attributes,
        );
    }
}
