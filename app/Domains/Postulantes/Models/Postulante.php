<?php

namespace App\Domains\Postulantes\Models;

use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nombres_post',
    'apellidos_post',
    'ci_post',
    'email_post',
    'celular_post',
    'edad_post',
    'id_col',
    'id_car',
    'turno_post',
    'gestion_post',
    'estado_post',
    'observaciones_post',
])]
class Postulante extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_post';

    protected function casts(): array
    {
        return [
            'edad_post' => 'integer',
            'gestion_post' => 'integer',
        ];
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class, 'id_col', 'id_col');
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
}
