<?php

namespace App\Domains\Resultados\Models;

use App\Domains\Evaluaciones\Models\Alternativa;
use App\Domains\Evaluaciones\Models\Pregunta;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_eval_apl',
    'id_preg',
    'id_alt',
    'respuesta_texto_resp',
    'es_correcta_resp',
    'puntaje_obtenido_resp',
    'puntaje_maximo_resp',
    'tiempo_segundos_resp',
    'intentos_resp',
    'orden_resp',
])]
class RespuestaEvaluacion extends Model
{
    use SoftDeletes;

    protected $table = 'respuestas_evaluacion';

    protected $primaryKey = 'id_resp_eval';

    protected function casts(): array
    {
        return [
            'es_correcta_resp' => 'boolean',
            'puntaje_obtenido_resp' => 'decimal:2',
            'puntaje_maximo_resp' => 'decimal:2',
            'tiempo_segundos_resp' => 'integer',
            'intentos_resp' => 'integer',
            'orden_resp' => 'integer',
        ];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionAplicada::class, 'id_eval_apl', 'id_eval_apl');
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class, 'id_preg', 'id_preg');
    }

    public function alternativa(): BelongsTo
    {
        return $this->belongsTo(Alternativa::class, 'id_alt', 'id_alt');
    }
}
