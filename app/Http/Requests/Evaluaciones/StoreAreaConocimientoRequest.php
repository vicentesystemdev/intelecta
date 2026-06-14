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
            'id_mat' => ['required', 'integer', 'exists:materias,id_mat'],
            'nombre_area' => ['required', 'string', 'max:120', 'unique:areas_conocimiento,nombre_area'],
            'descripcion_area' => ['nullable', 'string', 'max:2000'],
            'estado_area' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_mat.required' => 'Seleccione la materia a la que pertenece el área de conocimiento.',
            'id_mat.integer' => 'La materia seleccionada no tiene un identificador válido.',
            'id_mat.exists' => 'La materia seleccionada no existe.',
            'nombre_area.required' => 'El nombre del área de conocimiento es obligatorio.',
            'nombre_area.max' => 'El nombre del área no puede superar los 120 caracteres.',
            'nombre_area.unique' => 'Ya existe un área de conocimiento con este nombre.',
            'descripcion_area.max' => 'La descripción no puede superar los 2000 caracteres.',
            'estado_area.required' => 'Seleccione el estado del área de conocimiento.',
            'estado_area.in' => 'Seleccione un estado válido para el área de conocimiento.',
        ];
    }
}
