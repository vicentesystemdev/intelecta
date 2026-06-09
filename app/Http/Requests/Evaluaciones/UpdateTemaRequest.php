<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('temas.editar') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_area' => ['required', 'integer', 'exists:areas_conocimiento,id_area'],
            'nombre_tem' => [
                'required',
                'string',
                'max:120',
                Rule::unique('temas', 'nombre_tem')
                    ->where('id_area', $this->integer('id_area'))
                    ->ignore($this->route('tema')->id_tem, 'id_tem'),
            ],
            'descripcion_tem' => ['nullable', 'string', 'max:2000'],
            'nivel_tem' => ['nullable', 'in:basico,intermedio,avanzado'],
            'estado_tem' => ['required', 'in:activo,inactivo'],
        ];
    }
}
