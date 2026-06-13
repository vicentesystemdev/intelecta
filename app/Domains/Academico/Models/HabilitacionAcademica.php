<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_post',
    'id_insc',
    'id_mat',
    'estado_hab',
    'motivo_hab',
    'fecha_inicio_hab',
    'fecha_fin_hab',
    'habilitado_evaluaciones_hab',
    'habilitado_simulacros_hab',
    'habilitado_reportes_hab',
    'observacion_hab',
])]
class HabilitacionAcademica extends Model
{
    use SoftDeletes;

    protected $table = 'habilitaciones_academicas';

    protected $primaryKey = 'id_hab';

    protected function casts(): array
    {
        return [
            'fecha_inicio_hab' => 'date',
            'fecha_fin_hab' => 'date',
            'habilitado_evaluaciones_hab' => 'boolean',
            'habilitado_simulacros_hab' => 'boolean',
            'habilitado_reportes_hab' => 'boolean',
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'id_post', 'id_post');
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(InscripcionAcademica::class, 'id_insc', 'id_insc');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(MatriculaAcademica::class, 'id_mat', 'id_mat');
    }
}
