<?php

namespace App\Domains\Institucional\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre_col',
    'tipo_col',
    'ubicacion_col',
    'estado_col',
])]
class Colegio extends Model
{
    protected $primaryKey = 'id_col';

    public function postulantes(): HasMany
    {
        return $this->hasMany(Postulante::class, 'id_col', 'id_col');
    }
}
