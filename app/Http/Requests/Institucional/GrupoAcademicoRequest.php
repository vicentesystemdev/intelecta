<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrupoAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $grupo = $this->route('grupo');

        return [
            'id_prog' => ['required', 'integer', 'exists:programas_academicos,id_prog'],
            'nombre_grupo' => ['required', 'string', 'max:160'],
            'codigo_grupo' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('grupos_academicos', 'codigo_grupo')
                    ->where(fn ($query) => $query->where('id_prog', $this->integer('id_prog')))
                    ->ignore($grupo?->id_grupo, 'id_grupo'),
            ],
            'turno_grupo' => ['nullable', 'string', 'max:80'],
            'aula_grupo' => ['nullable', 'string', 'max:80'],
            'capacidad_grupo' => ['required', 'integer', 'min:1', 'max:500'],
            'nivel_grupo' => ['nullable', 'string', 'max:100'],
            'tutor_responsable_grupo' => ['nullable', 'string', 'max:180'],
            'estado_grupo' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_prog.required' => 'Seleccione un programa académico.',
            'id_prog.exists' => 'El programa académico seleccionado no existe.',
            'nombre_grupo.required' => 'El nombre del grupo o paralelo es obligatorio.',
            'codigo_grupo.unique' => 'El código ya está registrado en este programa académico.',
            'capacidad_grupo.required' => 'La capacidad del grupo es obligatoria.',
            'capacidad_grupo.integer' => 'La capacidad debe ser un número entero.',
            'capacidad_grupo.min' => 'La capacidad debe ser mayor a cero.',
            'estado_grupo.in' => 'Seleccione un estado válido para el grupo.',
        ];
    }
}
