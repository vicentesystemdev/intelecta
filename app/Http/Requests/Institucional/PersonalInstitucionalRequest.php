<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Institucional\Models\Cargo;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PersonalInstitucionalRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageOrganization($this->isMethod('post') ? 'personal.crear' : 'personal.editar') ?? false;
    }

    public function rules(): array
    {
        $personal = $this->route('personal');
        $presence = fn (string $field): string => $personal && blank($personal->{$field}) && blank($this->input($field))
            ? 'nullable'
            : 'required';

        return [
            'nombres' => ['required', ...InputRules::person()],
            'apellidos' => ['required', ...InputRules::person()],
            'ci' => [$presence('ci'), ...InputRules::document(), Rule::unique('personal_institucional', 'ci')->ignore($personal?->id_personal, 'id_personal')],
            'celular' => [$presence('celular'), ...InputRules::phone()],
            'correo_contacto' => [$presence('correo_contacto'), ...InputRules::email()],
            'cargo_id' => [$presence('cargo_id'), 'integer', 'exists:cargos,id_cargo'],
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

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('cargo_id') || ! $this->filled('cargo_id')) {
                return;
            }

            $personal = $this->route('personal');
            $unchanged = $personal && (int) $personal->cargo_id === $this->integer('cargo_id');
            $active = Cargo::query()
                ->whereKey($this->integer('cargo_id'))
                ->where('estado', 'activo')
                ->exists();

            if (! $unchanged && ! $active) {
                $validator->errors()->add('cargo_id', 'Seleccione un cargo institucional activo.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'ci.required' => 'El C.I. es obligatorio para registrar nuevo Personal.',
            'ci.unique' => 'Este C.I. ya pertenece a otro registro.',
            'celular.required' => 'El celular es obligatorio para registrar nuevo Personal.',
            'correo_contacto.required' => 'El correo de contacto es obligatorio para registrar nuevo Personal.',
            'correo_contacto.email' => 'Ingrese un correo electrónico válido.',
            'cargo_id.required' => 'Seleccione un cargo institucional.',
            'cargo_id.exists' => 'El cargo institucional seleccionado no existe.',
        ];
    }
}
