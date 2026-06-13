<?php

namespace App\Domains\Academico\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nombre_prog',
    'codigo_prog',
    'universidad_objetivo_prog',
    'carrera_area_prog',
    'modalidad_prog',
    'fecha_inicio_prog',
    'fecha_fin_prog',
    'descripcion_prog',
    'estado_prog',
])]
class ProgramaAcademico extends Model
{
    use SoftDeletes;

    protected $table = 'programas_academicos';

    protected $primaryKey = 'id_prog';

    protected function casts(): array
    {
        return [
            'fecha_inicio_prog' => 'date',
            'fecha_fin_prog' => 'date',
        ];
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoAcademico::class, 'id_prog', 'id_prog');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(InscripcionAcademica::class, 'id_prog', 'id_prog');
    }

    public function postulantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Postulante::class,
            'inscripciones_academicas',
            'id_prog',
            'id_post',
        )->withPivot([
            'id_insc',
            'id_grupo',
            'fecha_inscripcion',
            'estado_inscripcion',
            'observacion_inscripcion',
        ])->withTimestamps();
    }

    public function simulacros(): HasMany
    {
        return $this->hasMany(SimulacroProgramado::class, 'id_prog', 'id_prog');
    }

    public function rendimientos(): HasMany
    {
        return $this->hasMany(RendimientoPostulante::class, 'id_prog', 'id_prog');
    }

    public function asignacionesTutores(): HasMany
    {
        return $this->hasMany(AsignacionTutor::class, 'id_prog', 'id_prog');
    }

    public function asignacionTutorActiva(): HasOne
    {
        return $this->hasOne(AsignacionTutor::class, 'id_prog', 'id_prog')
            ->whereNull('id_grupo')
            ->where('estado_asig', 'activo')
            ->latestOfMany('id_asig');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(MatriculaAcademica::class, 'id_prog', 'id_prog');
    }
}
