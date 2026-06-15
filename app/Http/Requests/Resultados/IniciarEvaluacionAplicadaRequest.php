<?php

namespace App\Http\Requests\Resultados;

use Illuminate\Foundation\Http\FormRequest;

class IniciarEvaluacionAplicadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Estudiante', 'Postulante']) ?? false;
    }

    public function rules(): array
    {
        return [
            'id_sim' => ['nullable', 'integer', 'exists:simulacros_programados,id_sim'],
            'tipo_eval_apl' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_sim.exists' => 'El simulacro seleccionado no existe.',
            'tipo_eval_apl.max' => 'El tipo de evaluación no puede superar los 120 caracteres.',
        ];
    }
}
