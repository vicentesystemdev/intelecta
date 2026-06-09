<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaConocimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('areas.crear') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_area' => ['required', 'string', 'max:120', 'unique:areas_conocimiento,nombre_area'],
            'descripcion_area' => ['nullable', 'string', 'max:2000'],
            'estado_area' => ['required', 'in:activo,inactivo'],
        ];
    }
}
