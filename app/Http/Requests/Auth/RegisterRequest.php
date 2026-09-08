<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;

class RegisterRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', ...InputRules::person(255)],
            'email' => ['required', ...InputRules::email(), 'unique:users,email'],
            'password' => ['required', ...InputRules::password()],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
