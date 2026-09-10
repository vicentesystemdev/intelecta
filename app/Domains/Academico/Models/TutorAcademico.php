<?php

namespace App\Domains\Academico\Models;

use App\Domains\Institucional\Models\PersonalInstitucional;
use Database\Factories\TutorAcademicoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'personal_id',
    'especialidad_tutor',
    'formacion_tutor',
    'experiencia_tutor',
    'estado_tutor',
    'observacion_tutor',
])]
#[UseFactory(TutorAcademicoFactory::class)]
class TutorAcademico extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tutores_academicos';

    protected $primaryKey = 'id_tutor';

    protected $appends = ['nombre_completo'];

    // Identity is needed by all academic serializers, including reduced-column selects.
    protected $with = ['personal:id_personal,user_id,nombres,apellidos,celular,correo_contacto'];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(PersonalInstitucional::class, 'personal_id', 'id_personal');
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
        return trim(($this->personal?->nombres ?? '').' '.($this->personal?->apellidos ?? ''));
    }
}
