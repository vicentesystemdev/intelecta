<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;

class UpdatePasswordRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', ...InputRules::password()],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
