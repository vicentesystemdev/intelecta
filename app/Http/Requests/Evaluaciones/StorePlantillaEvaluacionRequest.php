<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePlantillaEvaluacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('plantillas.crear') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_plan' => ['required', 'string', 'max:180'],
            'descripcion_plan' => ['nullable', 'string', 'max:3000'],
            'objetivo_plan' => ['nullable', 'string', 'max:3000'],
            'duracion_minutos_plan' => ['nullable', 'integer', 'min:1', 'max:480'],
            'dificultad_plan' => ['nullable', 'in:basica,basica-media,media,media-alta,avanzada,mixta'],
            'estado_plan' => ['required', 'in:activa,inactiva'],
            'preguntas' => ['required', 'array', 'min:1'],
            'preguntas.*.id_preg' => ['required', 'integer', 'distinct', 'exists:preguntas,id_preg'],
            'preguntas.*.orden_pp' => ['required', 'integer', 'min:1'],
            'preguntas.*.puntaje_pp' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $total = collect($this->input('preguntas', []))->sum(
                    fn (array $pregunta) => (float) ($pregunta['puntaje_pp'] ?? 0)
                );

                if (abs($total - 100) > 0.001) {
                    $validator->errors()->add('preguntas', 'La ponderación total de la plantilla debe sumar exactamente 100 puntos.');
                }
            },
        ];
    }
}
