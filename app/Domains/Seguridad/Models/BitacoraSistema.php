<?php

namespace App\Domains\Seguridad\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'nombre_usuario',
    'correo_usuario',
    'rol_usuario',
    'accion',
    'modulo',
    'entidad',
    'entidad_id',
    'descripcion',
    'valores_anteriores',
    'valores_nuevos',
    'ip',
    'user_agent',
    'metodo_http',
    'ruta',
    'url',
    'severidad',
])]
class BitacoraSistema extends Model
{
    protected $table = 'bitacora_sistema';

    protected $primaryKey = 'id_bitacora';

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
