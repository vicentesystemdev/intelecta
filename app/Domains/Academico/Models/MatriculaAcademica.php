<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_insc',
    'id_post',
    'id_prog',
    'id_grupo',
    'codigo_mat',
    'fecha_matricula_mat',
    'monto_matricula_mat',
    'estado_matricula_mat',
    'tipo_beneficio_mat',
    'observacion_mat',
])]
class MatriculaAcademica extends Model
{
    use SoftDeletes;

    protected $table = 'matriculas_academicas';

    protected $primaryKey = 'id_mat';

    protected function casts(): array
    {
        return [
            'fecha_matricula_mat' => 'date',
            'monto_matricula_mat' => 'decimal:2',
        ];
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(InscripcionAcademica::class, 'id_insc', 'id_insc');
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

    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaAcademica::class, 'id_mat', 'id_mat');
    }

    public function habilitacion(): HasOne
    {
        return $this->hasOne(HabilitacionAcademica::class, 'id_mat', 'id_mat');
    }
}
