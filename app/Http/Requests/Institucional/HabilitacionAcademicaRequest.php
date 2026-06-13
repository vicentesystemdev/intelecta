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
}
