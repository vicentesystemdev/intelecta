<?php

namespace App\Domains\Evaluaciones\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_area',
    'nombre_tem',
    'descripcion_tem',
    'nivel_tem',
    'estado_tem',
])]
class Tema extends Model
{
    protected $primaryKey = 'id_tem';

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaConocimiento::class, 'id_area', 'id_area');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class, 'id_tem', 'id_tem');
    }
}
