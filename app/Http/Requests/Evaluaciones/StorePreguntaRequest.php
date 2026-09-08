<?php

namespace App\Http\Requests\Evaluaciones;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePreguntaRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('preguntas.crear') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_mat' => ['nullable', 'integer', 'exists:materias,id_mat'],
            'id_area' => ['nullable', 'integer', 'exists:areas_conocimiento,id_area'],
            'id_tem' => ['nullable', 'integer', 'exists:temas,id_tem'],
            'subtema_preg' => ['nullable', 'string', 'max:255'],
            'enunciado_preg' => ['required', 'string', 'max:5000'],
            'tipo_preg' => ['required', 'in:opcion_multiple,verdadero_falso,respuesta_corta'],
            'dificultad_preg' => ['nullable', 'in:basica,media,avanzada'],
            'exigencia_preg' => ['nullable', 'string', Rule::in(InputRules::options('exigencia_preg'))],
            'habilidad_preg' => ['nullable', 'string', Rule::in(InputRules::options('habilidad_preg'))],
            'tiempo_estimado_seg_preg' => ['nullable', 'integer', 'min:15', 'max:7200'],
            'relacion_ingenieria_preg' => ['nullable', 'string', 'max:2000'],
            'puntaje_preg' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100'],
            'explicacion_preg' => ['nullable', 'string', 'max:5000'],
            'estado_preg' => ['required', Rule::enum(EstadoRegistro::class)],
            'alternativas' => ['array', 'list', 'max:5'],
            'alternativas.*' => ['required', 'array:letra_alt,texto_alt,es_correcta_alt,orden_alt,estado_alt'],
            'alternativas.*.texto_alt' => ['required_with:alternativas', 'string', 'max:2000'],
            'alternativas.*.letra_alt' => ['nullable', 'distinct', 'in:A,B,C,D,E'],
            'alternativas.*.es_correcta_alt' => ['required_with:alternativas', 'boolean'],
            'alternativas.*.orden_alt' => ['required_with:alternativas', 'integer', 'distinct', 'min:1', 'max:5'],
            'alternativas.*.estado_alt' => ['nullable', Rule::enum(EstadoRegistro::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

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

    public function messages(): array
    {
        return [
            'id_tem.exists' => 'El tema académico seleccionado no existe.',
            'enunciado_preg.required' => 'El enunciado de la pregunta es obligatorio.',
            'tipo_preg.required' => 'Seleccione el tipo de pregunta.',
            'tipo_preg.in' => 'Seleccione un tipo de pregunta válido.',
            'dificultad_preg.in' => 'Seleccione una dificultad válida.',
            'tiempo_estimado_seg_preg.integer' => 'El tiempo estimado debe expresarse en segundos enteros.',
            'puntaje_preg.required' => 'El puntaje de la pregunta es obligatorio.',
            'puntaje_preg.numeric' => 'El puntaje debe ser numérico.',
            'puntaje_preg.min' => 'El puntaje debe ser mayor a cero.',
            'estado_preg.required' => 'Seleccione el estado de la pregunta.',
            'estado_preg.in' => 'Seleccione un estado válido para la pregunta.',
            'alternativas.*.texto_alt.required_with' => 'Complete el texto de todas las alternativas.',
            'alternativas.*.es_correcta_alt.required_with' => 'Indique cuál alternativa es correcta.',
        ];
    }
}
