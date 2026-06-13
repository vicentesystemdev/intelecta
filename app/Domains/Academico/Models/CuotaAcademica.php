<?php

namespace App\Domains\Academico\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_mat',
    'nro_cuota',
    'concepto_cuota',
    'monto_cuota',
    'fecha_vencimiento_cuota',
    'fecha_pago_cuota',
    'metodo_pago_cuota',
    'estado_cuota',
    'observacion_cuota',
])]
class CuotaAcademica extends Model
{
    use SoftDeletes;

    protected $table = 'cuotas_academicas';

    protected $primaryKey = 'id_cuota';

    protected function casts(): array
    {
        return [
            'nro_cuota' => 'integer',
            'monto_cuota' => 'decimal:2',
            'fecha_vencimiento_cuota' => 'date',
            'fecha_pago_cuota' => 'date',
        ];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(MatriculaAcademica::class, 'id_mat', 'id_mat');
    }
}
