<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
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
            'fecha_inicio_asig' => ['nullable', 'date'],
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
}
