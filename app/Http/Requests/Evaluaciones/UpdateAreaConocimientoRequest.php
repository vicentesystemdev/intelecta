<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaConocimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('areas.editar') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_area' => [
                'required',
                'string',
                'max:120',
                Rule::unique('areas_conocimiento', 'nombre_area')->ignore($this->route('area')->id_area, 'id_area'),
            ],
            'descripcion_area' => ['nullable', 'string', 'max:2000'],
            'estado_area' => ['required', 'in:activo,inactivo'],
        ];
    }
}
