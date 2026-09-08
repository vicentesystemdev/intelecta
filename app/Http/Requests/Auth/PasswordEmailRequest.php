<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;

class PasswordEmailRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', ...InputRules::email()],
        ];
    }
}
