<?php

namespace App\Domains\Evaluaciones\Models;

use App\Domains\Resultados\Models\EvaluacionAplicada;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nombre_plan',
    'descripcion_plan',
    'objetivo_plan',
    'duracion_minutos_plan',
    'dificultad_plan',
    'estado_plan',
])]
class PlantillaEvaluacion extends Model
{
    use SoftDeletes;

    protected $table = 'plantillas_evaluacion';

    protected $primaryKey = 'id_plan';

    public function preguntas(): BelongsToMany
    {
        return $this->belongsToMany(
            Pregunta::class,
            'plantilla_preguntas',
            'id_plan',
            'id_preg',
        )->withPivot(['orden_pp', 'puntaje_pp'])
            ->withTimestamps()
            ->orderByPivot('orden_pp');
    }

    public function evaluacionesAplicadas(): HasMany
    {
        return $this->hasMany(EvaluacionAplicada::class, 'id_plantilla', 'id_plan');
    }
}
