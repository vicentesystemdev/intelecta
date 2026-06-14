<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HabilitacionAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'estado_hab' => ['required', Rule::in(['habilitado', 'observado', 'restringido', 'temporal'])],
            'motivo_hab' => ['nullable', 'string', 'max:220'],
            'fecha_inicio_hab' => ['nullable', 'date'],
            'fecha_fin_hab' => ['nullable', 'date', 'after_or_equal:fecha_inicio_hab'],
            'habilitado_evaluaciones_hab' => ['required', 'boolean'],
            'habilitado_simulacros_hab' => ['required', 'boolean'],
            'habilitado_reportes_hab' => ['required', 'boolean'],
            'observacion_hab' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado_hab.required' => 'Seleccione el estado de habilitación académica.',
            'estado_hab.in' => 'Seleccione un estado válido de habilitación académica.',
            'fecha_inicio_hab.date' => 'La fecha de inicio no tiene un formato válido.',
            'fecha_fin_hab.date' => 'La fecha de finalización no tiene un formato válido.',
            'fecha_fin_hab.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio.',
            'habilitado_evaluaciones_hab.boolean' => 'El acceso a evaluaciones debe tener un valor válido.',
            'habilitado_simulacros_hab.boolean' => 'El acceso a simulacros debe tener un valor válido.',
            'habilitado_reportes_hab.boolean' => 'El acceso a reportes debe tener un valor válido.',
        ];
    }
}
