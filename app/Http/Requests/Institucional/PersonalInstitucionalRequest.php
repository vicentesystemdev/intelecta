<?php

namespace App\Http\Requests\Institucional;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class PersonalInstitucionalRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageOrganization($this->isMethod('post') ? 'personal.crear' : 'personal.editar') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', ...InputRules::person()],
            'apellidos' => ['required', ...InputRules::person()],
            'ci' => ['nullable', ...InputRules::document(), Rule::unique('personal_institucional', 'ci')->ignore($this->route('personal')?->id_personal, 'id_personal')],
            'celular' => ['nullable', ...InputRules::phone()],
            'correo_contacto' => ['nullable', ...InputRules::email()],
            'cargo_id' => ['nullable', 'integer', 'exists:cargos,id_cargo'],
            'user_id' => ['prohibited'],
            'estado' => ['prohibited'],
            'email' => ['prohibited'],
            'estado_cuenta' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'password' => ['prohibited'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'permissions' => ['prohibited'],
        ];
    }
}
