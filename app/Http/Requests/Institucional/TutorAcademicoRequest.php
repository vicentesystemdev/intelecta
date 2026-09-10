<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class TutorAcademicoRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can($this->isMethod('post') ? 'tutores.crear' : 'tutores.editar') ?? false)
            && (! $this->has('personal') || $this->user()?->canManageOrganization('personal.editar'));
    }

    public function rules(): array
    {
        $tutor = $this->route('tutor');
        $rawId = $this->input('personal_id');
        $personal = $tutor?->personal ?? (is_scalar($rawId) && ctype_digit((string) $rawId)
            ? PersonalInstitucional::find((int) $rawId) : null);

        return [
            'personal_id' => ['required', 'integer', 'exists:personal_institucional,id_personal',
                Rule::unique('tutores_academicos', 'personal_id')->ignore($tutor?->id_tutor, 'id_tutor')],
            'personal' => ['sometimes', 'array:nombres,apellidos,ci,celular,correo_contacto'],
            'personal.nombres' => ['required_with:personal', ...InputRules::person()],
            'personal.apellidos' => ['required_with:personal', ...InputRules::person()],
            'personal.ci' => ['nullable', ...InputRules::document(), Rule::unique('personal_institucional', 'ci')->ignore($personal?->id_personal, 'id_personal')],
            'personal.celular' => ['nullable', ...InputRules::phone()],
            'personal.correo_contacto' => ['nullable', ...InputRules::email()],
            'especialidad_tutor' => ['nullable', 'string', 'max:160'],
            'formacion_tutor' => ['nullable', 'string', 'max:220'],
            'experiencia_tutor' => ['nullable', 'string', 'max:3000'],
            'estado_tutor' => ['required', Rule::enum(EstadoRegistro::class)],
            'observacion_tutor' => ['nullable', 'string', 'max:2000'],
            'user_id' => ['prohibited'], 'email' => ['prohibited'], 'password' => ['prohibited'],
            'estado_cuenta' => ['prohibited'], 'email_verified_at' => ['prohibited'],
            'role' => ['prohibited'], 'roles' => ['prohibited'], 'permissions' => ['prohibited'],
            'cargo_id' => ['prohibited'], 'estado' => ['prohibited'],
            'nombres_tutor' => ['prohibited'], 'apellidos_tutor' => ['prohibited'],
            'ci_tutor' => ['prohibited'], 'celular_tutor' => ['prohibited'], 'correo_tutor' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'personal_id.required' => 'Selecciona Personal institucional existente.',
            'personal_id.unique' => 'Personal ya tiene un tutor, incluido uno archivado.',
            'personal_id.exists' => 'El Personal seleccionado no existe.',
        ];
    }
}
