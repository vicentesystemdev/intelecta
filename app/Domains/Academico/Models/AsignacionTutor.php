<?php

namespace App\Domains\Academico\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_tutor',
    'id_prog',
    'id_grupo',
    'materia_referencia_asig',
    'rol_asig',
    'fecha_inicio_asig',
    'fecha_fin_asig',
    'estado_asig',
    'observacion_asig',
])]
class AsignacionTutor extends Model
{
    use SoftDeletes;

    protected $table = 'asignaciones_tutores';

    protected $primaryKey = 'id_asig';

    protected function casts(): array
    {
        return [
            'fecha_inicio_asig' => 'date',
            'fecha_fin_asig' => 'date',
        ];
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(TutorAcademico::class, 'id_tutor', 'id_tutor');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaAcademico::class, 'id_prog', 'id_prog');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'id_grupo', 'id_grupo');
    }
}
