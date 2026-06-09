<?php

namespace App\Domains\Evaluaciones\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_preg',
    'texto_alt',
    'letra_alt',
    'es_correcta_alt',
    'orden_alt',
    'estado_alt',
])]
class Alternativa extends Model
{
    protected $primaryKey = 'id_alt';

    protected function casts(): array
    {
        return ['es_correcta_alt' => 'boolean'];
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class, 'id_preg', 'id_preg');
    }
}
