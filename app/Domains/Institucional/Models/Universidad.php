<?php

namespace App\Domains\Institucional\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nombre_uni',
    'sigla_uni',
    'tipo_uni',
    'departamento_uni',
    'nivel_exigencia_matematica_uni',
    'estado_uni',
])]
class Universidad extends Model
{
    protected $table = 'universidades';

    protected $primaryKey = 'id_uni';

    public function carreras(): HasMany
    {
        return $this->hasMany(Carrera::class, 'id_uni', 'id_uni');
    }
}
