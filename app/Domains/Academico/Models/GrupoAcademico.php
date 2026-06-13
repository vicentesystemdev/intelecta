<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_prog',
    'nombre_grupo',
    'codigo_grupo',
    'turno_grupo',
    'aula_grupo',
    'capacidad_grupo',
    'nivel_grupo',
    'tutor_responsable_grupo',
    'estado_grupo',
])]
class GrupoAcademico extends Model
{
    use SoftDeletes;

    protected $table = 'grupos_academicos';

    protected $primaryKey = 'id_grupo';

    protected function casts(): array
    {
        return ['capacidad_grupo' => 'integer'];
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaAcademico::class, 'id_prog', 'id_prog');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionAcademica::class, 'id_grupo', 'id_grupo');
    }

    public function postulantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Postulante::class,
            'inscripciones_academicas',
            'id_grupo',
            'id_post',
        )->withPivot(['id_insc', 'id_prog', 'estado_inscripcion'])->withTimestamps();
    }

    public function simulacros(): HasMany
    {
        return $this->hasMany(SimulacroProgramado::class, 'id_grupo', 'id_grupo');
    }

    public function rendimientos(): HasMany
    {
        return $this->hasMany(RendimientoPostulante::class, 'id_grupo', 'id_grupo');
    }

    public function asignacionesTutores(): HasMany
    {
        return $this->hasMany(AsignacionTutor::class, 'id_grupo', 'id_grupo');
    }

    public function asignacionTutorActiva(): HasOne
    {
        return $this->hasOne(AsignacionTutor::class, 'id_grupo', 'id_grupo')
            ->where('estado_asig', 'activo')
            ->latestOfMany('id_asig');
    }
}
