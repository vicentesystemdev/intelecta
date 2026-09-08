<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\NormalizedFormRequest;
use App\Models\User;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.editar') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $usuario */
        $usuario = $this->route('usuario');

        return [
            'name' => ['required', ...InputRules::person(255)],
            'email' => [
                'required',
                ...InputRules::email(),
                Rule::unique('users', 'email')->ignore($usuario->getKey()),
            ],
            'password' => ['nullable', ...InputRules::password()],
            'password_confirmation' => ['nullable', 'required_with:password', 'string'],
            'role' => [
                'required',
                'string',
                'max:255',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
        ];
    }
}
