<?php

namespace App\Domains\Institucional\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_uni',
    'nombre_car',
    'area_car',
    'nivel_exigencia_matematica_car',
    'estado_car',
])]
class Carrera extends Model
{
    protected $primaryKey = 'id_car';

    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'id_uni', 'id_uni');
    }

    public function postulantes(): HasMany
    {
        return $this->hasMany(Postulante::class, 'id_car', 'id_car');
    }
}
