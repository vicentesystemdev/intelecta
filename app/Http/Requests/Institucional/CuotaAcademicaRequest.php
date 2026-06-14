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
            'fecha_pago_cuota' => [
                'nullable',
                'required_if:estado_cuota,pagada',
                'date',
                'before_or_equal:today',
            ],
            'metodo_pago_cuota' => ['nullable', 'string', 'max:120'],
            'estado_cuota' => ['required', Rule::in(['pendiente', 'pagada', 'vencida', 'becada', 'exenta'])],
            'observacion_cuota' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_mat.required' => 'Seleccione una matrícula académica.',
            'id_mat.exists' => 'La matrícula académica seleccionada no existe.',
            'nro_cuota.integer' => 'El número de cuota debe ser un número entero.',
            'nro_cuota.min' => 'El número de cuota debe ser mayor a cero.',
            'nro_cuota.unique' => 'Este número de cuota ya está registrado para la matrícula.',
            'monto_cuota.required' => 'El monto de la cuota es obligatorio.',
            'monto_cuota.numeric' => 'El monto de la cuota debe ser numérico.',
            'monto_cuota.min' => 'El monto de la cuota no puede ser negativo.',
            'fecha_vencimiento_cuota.date' => 'La fecha de vencimiento no tiene un formato válido.',
            'fecha_pago_cuota.date' => 'La fecha de pago no tiene un formato válido.',
            'fecha_pago_cuota.required_if' => 'La fecha de pago es obligatoria cuando la cuota está pagada.',
            'fecha_pago_cuota.before_or_equal' => 'La fecha de pago no puede ser posterior a la fecha actual.',
            'estado_cuota.required' => 'Seleccione el estado de la cuota académica.',
            'estado_cuota.in' => 'Seleccione un estado válido para la cuota académica.',
        ];
    }
}
