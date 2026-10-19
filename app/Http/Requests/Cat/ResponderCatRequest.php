<?php

namespace App\Http\Requests\Cat;

use Illuminate\Foundation\Http\FormRequest;

class ResponderCatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Estudiante') ?? false;
    }

    public function rules(): array
    {
        // No existe exists:alternativas: los IDs válidos pertenecen al snapshot.
        return ['id_preg' => ['required', 'integer', 'min:1'], 'id_alt' => ['required', 'integer', 'min:1'],
            'revision' => ['required', 'integer', 'min:0', 'max:10']];
    }
}
