<?php

namespace App\Http\Requests\Evaluaciones;

class UpdateAreaConocimientoRequest extends StoreAreaConocimientoRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('areas.editar') ?? false;
    }
}
