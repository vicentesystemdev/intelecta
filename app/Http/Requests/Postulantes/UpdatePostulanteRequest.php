<?php

namespace App\Http\Requests\Postulantes;

use App\Rules\PostulanteBirthDate;

class UpdatePostulanteRequest extends StorePostulanteRequest
{
    protected function birthDateRules(): array
    {
        return [
            'sometimes',
            $this->route('postulante')?->fecha_nacimiento_post !== null ? 'required' : 'nullable',
            'string',
            new PostulanteBirthDate(registration: false),
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('postulantes.editar') ?? false;
    }
}
