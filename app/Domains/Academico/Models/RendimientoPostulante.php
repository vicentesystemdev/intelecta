<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_post',
    'id_prog',
    'id_grupo',
    'promedio_general_rend',
    'promedio_matematica_rend',
    'promedio_fisica_rend',
    'promedio_quimica_rend',
    'promedio_paa_rend',
    'asistencia_porcentaje_rend',
    'nivel_riesgo_rend',
    'observacion_rend',
])]
class RendimientoPostulante extends Model
{
    protected $table = 'rendimientos_postulante';

    protected $primaryKey = 'id_rend';

    protected function casts(): array
    {
        return [
            'promedio_general_rend' => 'decimal:2',
            'promedio_matematica_rend' => 'decimal:2',
            'promedio_fisica_rend' => 'decimal:2',
            'promedio_quimica_rend' => 'decimal:2',
            'promedio_paa_rend' => 'decimal:2',
            'asistencia_porcentaje_rend' => 'decimal:2',
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'id_post', 'id_post');
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
