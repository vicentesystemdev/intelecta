<?php

namespace App\Domains\Cat\Models;

use App\Domains\Evaluaciones\Models\Pregunta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentoCat extends Model
{
    protected $table = 'instrumentos_cat';

    protected $primaryKey = 'id_instrumento_cat';

    protected $guarded = ['id_instrumento_cat'];

    protected function casts(): array
    {
        return ['configuracion' => 'array', 'demo' => 'boolean', 'duracion_minutos' => 'integer'];
    }

    public function preguntas(): BelongsToMany
    {
        return $this->belongsToMany(Pregunta::class, 'instrumento_cat_preguntas', 'id_instrumento_cat', 'id_preg')
            ->withPivot(['a', 'b', 'c', 'origen'])->orderBy('preguntas.id_preg');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(IntentoCat::class, 'id_instrumento_cat');
    }
}
