<?php

namespace App\Domains\Postulantes\Models;

use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Postulantes\Support\BirthDate;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Models\User;
use Database\Factories\PostulanteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(PostulanteFactory::class)]
#[Fillable([
    'nombres_post',
    'apellidos_post',
    'ci_post',
    'email_post',
    'celular_post',
    'edad_post',
    'fecha_nacimiento_post',
    'id_col',
    'id_car',
    'turno_post',
    'gestion_post',
    'estado_post',
    'observaciones_post',
])]
class Postulante extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'id_post';

    protected $appends = ['edad_actual'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'edad_post' => 'integer',
            'fecha_nacimiento_post' => 'date:Y-m-d',
            'gestion_post' => 'integer',
        ];
    }

    protected function edadActual(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->fecha_nacimiento_post !== null
            ? BirthDate::age($this->fecha_nacimiento_post->format('Y-m-d'))
            // Temporary legacy compatibility: preserve the historical value, never invent a date.
            : $this->edad_post);
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class, 'id_col', 'id_col');
    }

    // Deliberately not fillable: ordinary academic forms cannot reassign identity.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_car', 'id_car');
    }

    public function universidad(): HasOneThrough
    {
        return $this->hasOneThrough(
            Universidad::class,
            Carrera::class,
            'id_car',
            'id_uni',
            'id_car',
            'id_uni',
        );
    }

    public function inscripcionesAcademicas(): HasMany
    {
        return $this->hasMany(InscripcionAcademica::class, 'id_post', 'id_post');
    }

    public function rendimientosAcademicos(): HasMany
    {
        return $this->hasMany(RendimientoPostulante::class, 'id_post', 'id_post');
    }

    public function matriculasAcademicas(): HasMany
    {
        return $this->hasMany(MatriculaAcademica::class, 'id_post', 'id_post');
    }

    public function habilitacionesAcademicas(): HasMany
    {
        return $this->hasMany(HabilitacionAcademica::class, 'id_post', 'id_post');
    }

    public function asistenciasAcademicas(): HasMany
    {
        return $this->hasMany(AsistenciaAcademica::class, 'id_post', 'id_post');
    }

    public function evaluacionesAplicadas(): HasMany
    {
        return $this->hasMany(EvaluacionAplicada::class, 'id_post', 'id_post');
    }
}
