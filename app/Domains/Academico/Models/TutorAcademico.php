<?php

namespace App\Domains\Academico\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'nombres_tutor',
    'apellidos_tutor',
    'ci_tutor',
    'celular_tutor',
    'correo_tutor',
    'especialidad_tutor',
    'formacion_tutor',
    'experiencia_tutor',
    'estado_tutor',
    'observacion_tutor',
])]
class TutorAcademico extends Model
{
    use SoftDeletes;

    protected $table = 'tutores_academicos';

    protected $primaryKey = 'id_tutor';

    protected $appends = ['nombre_completo'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionTutor::class, 'id_tutor', 'id_tutor');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaAcademica::class, 'id_tutor', 'id_tutor');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres_tutor} {$this->apellidos_tutor}");
    }
}
