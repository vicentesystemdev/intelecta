<?php

namespace App\Domains\Evaluaciones\Models;

use App\Domains\Resultados\Models\RespuestaEvaluacion;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_tem',
    'subtema_preg',
    'enunciado_preg',
    'tipo_preg',
    'dificultad_preg',
    'exigencia_preg',
    'habilidad_preg',
    'tiempo_estimado_seg_preg',
    'relacion_ingenieria_preg',
    'puntaje_preg',
    'explicacion_preg',
    'estado_preg',
])]
class Pregunta extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_preg';

    protected function casts(): array
    {
        return [
            'puntaje_preg' => 'decimal:2',
            'tiempo_estimado_seg_preg' => 'integer',
        ];
    }

    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class, 'id_tem', 'id_tem');
    }

    public function alternativas(): HasMany
    {
        return $this->hasMany(Alternativa::class, 'id_preg', 'id_preg')
            ->orderBy('orden_alt');
    }

    public function plantillas(): BelongsToMany
    {
        return $this->belongsToMany(
            PlantillaEvaluacion::class,
            'plantilla_preguntas',
            'id_preg',
            'id_plan',
        )->withPivot(['orden_pp', 'puntaje_pp'])->withTimestamps();
    }

    public function respuestasEvaluacion(): HasMany
    {
        return $this->hasMany(RespuestaEvaluacion::class, 'id_preg', 'id_preg');
    }
}
