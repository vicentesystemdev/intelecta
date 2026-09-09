<?php

namespace App\Http\Requests;

use App\Support\Validation\InputRules;
use Illuminate\Contracts\Validation\ValidationRule;

class ProfileUpdateRequest extends NormalizedFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', ...InputRules::person(255)],
            'email' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return ['email.prohibited' => 'El correo de acceso solo puede ser modificado por TI desde la administración de usuarios.'];
    }
}
