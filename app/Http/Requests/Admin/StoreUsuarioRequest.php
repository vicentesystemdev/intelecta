<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;

class StoreUsuarioRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canChangeLoginEmail() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', ...InputRules::person(255)],
            'email' => ['required', ...InputRules::email(254), 'unique:users,email'],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'estado_cuenta' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'role' => ['missing'],
            'roles' => ['missing'],
        ];
    }
}
