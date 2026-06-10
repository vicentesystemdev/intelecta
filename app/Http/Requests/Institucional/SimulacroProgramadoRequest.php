<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulacroProgramadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'id_prog' => ['required', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => [
                'nullable',
                'integer',
                Rule::exists('grupos_academicos', 'id_grupo')
                    ->where(fn ($query) => $query->where('id_prog', $this->integer('id_prog'))),
            ],
            'id_plantilla' => ['nullable', 'integer', 'exists:plantillas_evaluacion,id_plan'],
            'titulo_sim' => ['required', 'string', 'max:180'],
            'fecha_sim' => ['nullable', 'date'],
            'hora_inicio_sim' => ['nullable', 'date_format:H:i'],
            'hora_fin_sim' => ['nullable', 'date_format:H:i', 'after:hora_inicio_sim'],
            'modalidad_sim' => ['nullable', 'string', 'max:100'],
            'estado_sim' => ['required', 'in:programado,en preparación,aplicado,cerrado'],
            'observacion_sim' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
