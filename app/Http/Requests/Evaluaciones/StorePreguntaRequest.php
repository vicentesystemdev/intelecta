<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePreguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('preguntas.crear') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_tem' => ['nullable', 'integer', 'exists:temas,id_tem'],
            'enunciado_preg' => ['required', 'string', 'max:5000'],
            'tipo_preg' => ['required', 'in:opcion_multiple,verdadero_falso,respuesta_corta'],
            'dificultad_preg' => ['nullable', 'in:basica,media,avanzada'],
            'puntaje_preg' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'explicacion_preg' => ['nullable', 'string', 'max:5000'],
            'estado_preg' => ['required', 'in:activo,inactivo'],
            'alternativas' => ['array'],
            'alternativas.*.texto_alt' => ['required_with:alternativas', 'string', 'max:2000'],
            'alternativas.*.letra_alt' => ['nullable', 'in:A,B,C,D,E'],
            'alternativas.*.es_correcta_alt' => ['required_with:alternativas', 'boolean'],
            'alternativas.*.orden_alt' => ['required_with:alternativas', 'integer', 'min:1', 'max:5'],
            'alternativas.*.estado_alt' => ['nullable', 'in:activo,inactivo'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $tipo = $this->input('tipo_preg');
                $alternativas = $this->input('alternativas', []);
                $cantidadEsperada = $tipo === 'opcion_multiple' ? 5 : ($tipo === 'verdadero_falso' ? 2 : 0);

                if (count($alternativas) !== $cantidadEsperada) {
                    $validator->errors()->add(
                        'alternativas',
                        "El tipo seleccionado requiere {$cantidadEsperada} alternativas."
                    );
                }

                if ($tipo !== 'respuesta_corta') {
                    $correctas = collect($alternativas)->where('es_correcta_alt', true)->count();

                    if ($correctas !== 1) {
                        $validator->errors()->add('alternativas', 'Debe seleccionar exactamente una alternativa correcta.');
                    }
                }
            },
        ];
    }
}
