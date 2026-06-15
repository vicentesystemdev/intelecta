<?php

namespace App\Http\Requests\Resultados;

use Illuminate\Foundation\Http\FormRequest;

class EnviarRespuestasEvaluacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Estudiante', 'Postulante']) ?? false;
    }

    public function rules(): array
    {
        return [
            'respuestas' => ['required', 'array', 'min:1'],
            'respuestas.*.id_preg' => [
                'required',
                'integer',
                'distinct',
                'exists:preguntas,id_preg',
            ],
            'respuestas.*.id_alt' => [
                'nullable',
                'integer',
                'exists:alternativas,id_alt',
            ],
            'respuestas.*.respuesta_texto' => ['nullable', 'string', 'max:5000'],
            'respuestas.*.tiempo_segundos' => ['nullable', 'integer', 'min:0'],
            'respuestas.*.intentos' => ['nullable', 'integer', 'min:1'],
            'tiempo_total_segundos' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'respuestas.required' => 'Debe registrar al menos una respuesta antes de finalizar.',
            'respuestas.array' => 'Las respuestas enviadas no tienen un formato válido.',
            'respuestas.*.id_preg.required' => 'Cada respuesta debe identificar su pregunta.',
            'respuestas.*.id_preg.distinct' => 'Una pregunta no puede enviarse más de una vez.',
            'respuestas.*.id_preg.exists' => 'Una de las preguntas enviadas no existe.',
            'respuestas.*.id_alt.exists' => 'Una de las alternativas seleccionadas no existe.',
            'respuestas.*.tiempo_segundos.min' => 'El tiempo por respuesta no puede ser negativo.',
            'tiempo_total_segundos.min' => 'El tiempo total no puede ser negativo.',
        ];
    }
}
