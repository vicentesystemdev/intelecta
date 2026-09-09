<?php

namespace App\Http\Requests\Resultados;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;

class EnviarRespuestasEvaluacionRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Estudiante') ?? false;
    }

    public function rules(): array
    {
        return [
            'respuestas' => ['required', 'array', 'list', 'min:1', 'max:'.InputRules::MAX_QUESTIONS],
            'respuestas.*' => ['required', 'array:id_preg,id_alt,respuesta_texto,tiempo_segundos,intentos'],
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
            'respuestas.*.tiempo_segundos' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'respuestas.*.intentos' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'tiempo_total_segundos' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
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
