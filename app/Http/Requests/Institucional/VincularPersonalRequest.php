<?php

namespace App\Http\Requests\Institucional;

use App\Http\Requests\NormalizedFormRequest;

class VincularPersonalRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canChangeLoginEmail() ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
