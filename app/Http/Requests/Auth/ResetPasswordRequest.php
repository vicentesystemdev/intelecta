<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;

class ResetPasswordRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', ...InputRules::email()],
            'password' => ['required', ...InputRules::password()],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
