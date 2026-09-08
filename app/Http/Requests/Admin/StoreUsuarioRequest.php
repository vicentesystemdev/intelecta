<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios.crear') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', ...InputRules::person(255)],
            'email' => ['required', ...InputRules::email(254), 'unique:users,email'],
            'password' => ['required', ...InputRules::password()],
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
