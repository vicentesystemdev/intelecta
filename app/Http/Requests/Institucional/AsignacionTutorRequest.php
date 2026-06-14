<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsignacionTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $asignacion = $this->route('asignacion');

        return [
            'id_tutor' => ['required', 'integer', 'exists:tutores_academicos,id_tutor'],
            'id_prog' => [
                'nullable',
                'required_without:id_grupo',
                'integer',
                'exists:programas_academicos,id_prog',
            ],
            'id_grupo' => [
                'nullable',
                'required_without:id_prog',
                'integer',
                'exists:grupos_academicos,id_grupo',
            ],
            'materia_referencia_asig' => ['nullable', 'string', 'max:160'],
            'rol_asig' => ['nullable', 'string', 'max:120'],
            'fecha_inicio_asig' => [
                'bail',
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($asignacion): void {
                    if (
                        ! $value
                        || $asignacion
                        || $this->input('estado_asig') !== 'activo'
                    ) {
                        return;
                    }

                    if (Carbon::parse($value)->startOfDay()->lt(today())) {
                        $fail('La fecha de inicio de una asignación activa no puede ser anterior a la fecha actual.');
                    }
                },
            ],
            'fecha_fin_asig' => ['nullable', 'date', 'after_or_equal:fecha_inicio_asig'],
            'estado_asig' => ['required', Rule::in(['activo', 'inactivo'])],
            'observacion_asig' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->filled('id_prog') || ! $this->filled('id_grupo')) {
                    return;
                }

                $belongsToProgram = \App\Domains\Academico\Models\GrupoAcademico::query()
                    ->where('id_grupo', $this->integer('id_grupo'))
                    ->where('id_prog', $this->integer('id_prog'))
                    ->exists();

                if (! $belongsToProgram) {
                    $validator->errors()->add(
                        'id_grupo',
                        'El grupo seleccionado no pertenece al programa académico indicado.',
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'id_tutor.required' => 'Seleccione un tutor académico.',
            'id_tutor.exists' => 'El tutor académico seleccionado no existe.',
            'id_prog.required_without' => 'Seleccione un programa académico o un grupo.',
            'id_grupo.required_without' => 'Seleccione un grupo o un programa académico.',
            'fecha_inicio_asig.date' => 'La fecha de inicio no tiene un formato válido.',
            'fecha_fin_asig.date' => 'La fecha de finalización no tiene un formato válido.',
            'fecha_fin_asig.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio.',
            'estado_asig.required' => 'Seleccione el estado de la asignación tutorial.',
            'estado_asig.in' => 'Seleccione un estado válido para la asignación tutorial.',
        ];
    }
}
