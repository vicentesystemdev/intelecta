<?php

namespace App\Domains\Resultados\Models;

use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_post',
    'id_plantilla',
    'id_sim',
    'codigo_eval_apl',
    'tipo_eval_apl',
    'fecha_inicio_eval_apl',
    'fecha_fin_eval_apl',
    'estado_eval_apl',
    'puntaje_total_eval_apl',
    'puntaje_maximo_eval_apl',
    'porcentaje_eval_apl',
    'tiempo_total_segundos_eval_apl',
    'intentos_eval_apl',
    'observacion_eval_apl',
])]
class EvaluacionAplicada extends Model
{
    use SoftDeletes;

    protected $table = 'evaluaciones_aplicadas';

    protected $primaryKey = 'id_eval_apl';

    protected function casts(): array
    {
        return [
            'fecha_inicio_eval_apl' => 'datetime',
            'fecha_fin_eval_apl' => 'datetime',
            'puntaje_total_eval_apl' => 'decimal:2',
            'puntaje_maximo_eval_apl' => 'decimal:2',
            'porcentaje_eval_apl' => 'decimal:2',
            'tiempo_total_segundos_eval_apl' => 'integer',
            'intentos_eval_apl' => 'integer',
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'id_post', 'id_post');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaEvaluacion::class, 'id_plantilla', 'id_plan');
    }

    public function simulacro(): BelongsTo
    {
        return $this->belongsTo(SimulacroProgramado::class, 'id_sim', 'id_sim');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaEvaluacion::class, 'id_eval_apl', 'id_eval_apl')
            ->orderBy('orden_resp');
    }
}
