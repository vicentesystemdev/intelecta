<?php

namespace App\Domains\Cat\Models;

use App\Domains\Cat\Casts\InstanteCat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaCat extends Model
{
    protected $table = 'respuestas_cat';

    protected $primaryKey = 'id_respuesta_cat';

    public $timestamps = false;

    protected $guarded = ['id_respuesta_cat'];

    protected function casts(): array
    {
        return ['correcta' => 'boolean', 'revision' => 'integer', 'metricas_cat' => 'array', 'respondida_en' => InstanteCat::class];
    }

    public function intento(): BelongsTo
    {
        return $this->belongsTo(IntentoCat::class, 'id_intento_cat');
    }
}
