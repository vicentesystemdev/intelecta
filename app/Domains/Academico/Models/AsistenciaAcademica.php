<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_prog',
    'id_grupo',
    'id_post',
    'id_tutor',
    'fecha_asist',
    'sesion_asist',
    'estado_asist',
    'observacion_asist',
])]
class AsistenciaAcademica extends Model
{
    use SoftDeletes;

    protected $table = 'asistencias_academicas';

    protected $primaryKey = 'id_asist';

    protected function casts(): array
    {
        return ['fecha_asist' => 'date'];
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

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(TutorAcademico::class, 'id_tutor', 'id_tutor');
    }
}
