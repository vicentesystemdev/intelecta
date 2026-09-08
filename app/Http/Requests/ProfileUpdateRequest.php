<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Validation\InputRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

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
            'email' => [
                'required',
                ...InputRules::email(),
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
