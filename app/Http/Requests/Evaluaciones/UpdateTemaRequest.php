<?php

namespace App\Http\Requests\Evaluaciones;

class UpdateTemaRequest extends StoreTemaRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('temas.editar') ?? false;
    }
}
