<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SimulacroProgramadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $simulacro = $this->route('simulacro');

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
            'fecha_sim' => [
                'bail',
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($simulacro): void {
                    if (! $value) {
                        return;
                    }

                    $existingDate = $simulacro?->fecha_sim?->format('Y-m-d');
                    $isHistoricalDateUnchanged = $existingDate && $existingDate === $value;

                    if (Carbon::parse($value)->startOfDay()->lt(today()) && ! $isHistoricalDateUnchanged) {
                        $fail('La fecha del simulacro no puede ser anterior a la fecha actual.');
                    }
                },
            ],
            'hora_inicio_sim' => ['nullable', 'date_format:H:i'],
            'hora_fin_sim' => ['nullable', 'date_format:H:i', 'after:hora_inicio_sim'],
            'modalidad_sim' => ['nullable', 'string', 'max:100'],
            'estado_sim' => ['required', 'in:programado,en preparación,aplicado,cerrado'],
            'observacion_sim' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_prog.required' => 'Seleccione un programa académico.',
            'id_prog.exists' => 'El programa académico seleccionado no existe.',
            'id_grupo.exists' => 'El grupo seleccionado no pertenece al programa académico.',
            'id_plantilla.exists' => 'La plantilla de evaluación seleccionada no existe.',
            'titulo_sim.required' => 'El título del simulacro es obligatorio.',
            'fecha_sim.date' => 'La fecha del simulacro no tiene un formato válido.',
            'hora_inicio_sim.date_format' => 'La hora de inicio no tiene un formato válido.',
            'hora_fin_sim.date_format' => 'La hora de finalización no tiene un formato válido.',
            'hora_fin_sim.after' => 'La hora de finalización debe ser posterior a la hora de inicio.',
            'estado_sim.required' => 'Seleccione el estado del simulacro programado.',
            'estado_sim.in' => 'Seleccione un estado válido para el simulacro.',
        ];
    }
}
