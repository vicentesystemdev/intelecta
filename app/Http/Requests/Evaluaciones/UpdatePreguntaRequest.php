<?php

namespace App\Http\Requests\Evaluaciones;

class UpdatePreguntaRequest extends StorePreguntaRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('preguntas.editar') ?? false;
    }
}
