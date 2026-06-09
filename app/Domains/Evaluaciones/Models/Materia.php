<?php

namespace App\Domains\Evaluaciones\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'codigo_mat',
    'nombre_mat',
    'descripcion_mat',
    'color_mat',
    'icono_mat',
    'estado_mat',
])]
class Materia extends Model
{
    protected $primaryKey = 'id_mat';

    public function areas(): HasMany
    {
        return $this->hasMany(AreaConocimiento::class, 'id_mat', 'id_mat');
    }
}
