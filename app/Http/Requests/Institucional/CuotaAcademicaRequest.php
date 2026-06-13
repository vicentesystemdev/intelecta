<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CuotaAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $cuota = $this->route('cuota');

        return [
            'id_mat' => ['required', 'integer', 'exists:matriculas_academicas,id_mat'],
            'nro_cuota' => [
                'nullable',
                'integer',
                'min:1',
                'max:120',
                Rule::unique('cuotas_academicas', 'nro_cuota')
                    ->where(fn ($query) => $query->where('id_mat', $this->integer('id_mat')))
                    ->ignore($cuota?->id_cuota, 'id_cuota'),
            ],
            'concepto_cuota' => ['nullable', 'string', 'max:180'],
            'monto_cuota' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'fecha_vencimiento_cuota' => ['nullable', 'date'],
            'fecha_pago_cuota' => ['nullable', 'date'],
            'metodo_pago_cuota' => ['nullable', 'string', 'max:120'],
            'estado_cuota' => ['required', Rule::in(['pendiente', 'pagada', 'vencida', 'becada', 'exenta'])],
            'observacion_cuota' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
