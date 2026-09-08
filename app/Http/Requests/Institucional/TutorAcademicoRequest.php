<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class TutorAcademicoRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'tutores.crear'
            : 'tutores.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $tutor = $this->route('tutor');

        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('tutores_academicos', 'user_id')
                    ->ignore($tutor?->id_tutor, 'id_tutor'),
            ],
            'nombres_tutor' => ['required', ...InputRules::person(120)],
            'apellidos_tutor' => ['required', ...InputRules::person(120)],
            'ci_tutor' => [
                'nullable',
                ...InputRules::document(),
                Rule::unique('tutores_academicos', 'ci_tutor')
                    ->ignore($tutor?->id_tutor, 'id_tutor'),
            ],
            'celular_tutor' => ['nullable', ...InputRules::phone()],
            'correo_tutor' => ['nullable', ...InputRules::email(180)],
            'especialidad_tutor' => ['nullable', 'string', 'max:160'],
            'formacion_tutor' => ['nullable', 'string', 'max:220'],
            'experiencia_tutor' => ['nullable', 'string', 'max:3000'],
            'estado_tutor' => ['required', Rule::enum(EstadoRegistro::class)],
            'observacion_tutor' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'user_id.unique' => 'El usuario seleccionado ya está vinculado con otro tutor académico.',
            'nombres_tutor.required' => 'Los nombres del tutor académico son obligatorios.',
            'apellidos_tutor.required' => 'Los apellidos del tutor académico son obligatorios.',
            'ci_tutor.unique' => 'El C.I. ya está registrado para otro tutor académico.',
            'correo_tutor.email' => 'Ingrese un correo electrónico válido.',
            'estado_tutor.required' => 'Seleccione el estado del tutor académico.',
            'estado_tutor.in' => 'Seleccione un estado válido para el tutor académico.',
        ];
    }
}
