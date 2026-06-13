<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'id_prog',
    'id_grupo',
    'id_post',
    'fecha_inscripcion',
    'estado_inscripcion',
    'observacion_inscripcion',
])]
class InscripcionAcademica extends Model
{
    protected $table = 'inscripciones_academicas';

    protected $primaryKey = 'id_insc';

    protected function casts(): array
    {
        return ['fecha_inscripcion' => 'date'];
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaAcademico::class, 'id_prog', 'id_prog');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'id_grupo', 'id_grupo');
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'id_post', 'id_post');
    }

    public function matricula(): HasOne
    {
        return $this->hasOne(MatriculaAcademica::class, 'id_insc', 'id_insc');
    }

    public function habilitacion(): HasOne
    {
        return $this->hasOne(HabilitacionAcademica::class, 'id_insc', 'id_insc');
    }
}
