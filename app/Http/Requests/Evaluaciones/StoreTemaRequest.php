<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('temas.crear') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_area' => ['required', 'integer', 'exists:areas_conocimiento,id_area'],
            'nombre_tem' => [
                'required',
                'string',
                'max:120',
                Rule::unique('temas', 'nombre_tem')->where('id_area', $this->integer('id_area')),
            ],
            'descripcion_tem' => ['nullable', 'string', 'max:2000'],
            'nivel_tem' => ['nullable', 'in:basico,intermedio,avanzado'],
            'estado_tem' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_area.required' => 'Seleccione el área de conocimiento del tema.',
            'id_area.integer' => 'El área seleccionada no tiene un identificador válido.',
            'id_area.exists' => 'El área de conocimiento seleccionada no existe.',
            'nombre_tem.required' => 'El nombre del tema académico es obligatorio.',
            'nombre_tem.max' => 'El nombre del tema no puede superar los 120 caracteres.',
            'nombre_tem.unique' => 'Ya existe un tema con este nombre dentro del área seleccionada.',
            'descripcion_tem.max' => 'La descripción no puede superar los 2000 caracteres.',
            'nivel_tem.in' => 'Seleccione un nivel válido para el tema académico.',
            'estado_tem.required' => 'Seleccione el estado del tema académico.',
            'estado_tem.in' => 'Seleccione un estado válido para el tema académico.',
        ];
    }
}
