<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramaAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $programa = $this->route('programa');

        return [
            'nombre_prog' => ['required', 'string', 'max:180'],
            'codigo_prog' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('programas_academicos', 'codigo_prog')
                    ->ignore($programa?->id_prog, 'id_prog'),
            ],
            'universidad_objetivo_prog' => ['nullable', 'string', 'max:180'],
            'carrera_area_prog' => ['nullable', 'string', 'max:180'],
            'modalidad_prog' => ['nullable', 'string', 'max:100'],
            'fecha_inicio_prog' => ['nullable', 'date'],
            'fecha_fin_prog' => ['nullable', 'date', 'after_or_equal:fecha_inicio_prog'],
            'descripcion_prog' => ['nullable', 'string', 'max:3000'],
            'estado_prog' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_prog' => 'nombre del programa',
            'codigo_prog' => 'código',
            'universidad_objetivo_prog' => 'universidad objetivo',
            'carrera_area_prog' => 'carrera o área',
            'modalidad_prog' => 'modalidad',
            'fecha_inicio_prog' => 'fecha de inicio',
            'fecha_fin_prog' => 'fecha de finalización',
            'descripcion_prog' => 'descripción',
            'estado_prog' => 'estado',
        ];
    }
}
