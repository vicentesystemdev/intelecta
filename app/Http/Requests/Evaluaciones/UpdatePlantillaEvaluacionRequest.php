<?php

namespace App\Http\Requests\Evaluaciones;

class UpdatePlantillaEvaluacionRequest extends StorePlantillaEvaluacionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('plantillas.editar') ?? false;
    }
}
