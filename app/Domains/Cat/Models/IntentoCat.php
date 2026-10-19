<?php

namespace App\Domains\Cat\Models;

use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class IntentoCat extends Model
{
    protected $table = 'intentos_cat';

    protected $primaryKey = 'id_intento_cat';

    protected $guarded = ['id_intento_cat'];

    protected $hidden = ['snapshot', 'huella_snapshot', 'error_motor'];

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'resultado_cat' => 'array', 'calculo_pendiente' => 'boolean',
            'revision' => 'integer', 'id_preg_actual' => 'integer', 'iniciado_en' => 'immutable_datetime',
            'vence_en' => 'immutable_datetime', 'finalizado_en' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (self $intento) {
            if ($intento->isDirty(['snapshot', 'huella_snapshot', 'id_post', 'id_instrumento_cat', 'iniciado_en', 'vence_en'])
                || ($intento->getOriginal('version_motor') !== null && $intento->isDirty('version_motor'))) {
                throw new LogicException('La identidad, el snapshot, el plazo y la versión fijada del intento son inmutables.');
            }
        });
    }

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(InstrumentoCat::class, 'id_instrumento_cat');
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'id_post');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaCat::class, 'id_intento_cat')->orderBy('revision');
    }
}
