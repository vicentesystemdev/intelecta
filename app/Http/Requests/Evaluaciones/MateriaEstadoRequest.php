<?php

namespace App\Http\Requests\Evaluaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MateriaEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->cuentaActiva()
            && $this->user()->hasRole('Super Administrador')
            && $this->user()->can('materias.cambiar_estado');
    }

    public function rules(): array
    {
        return ['estado_mat' => ['required', Rule::in(['activo', 'inactivo'])]];
    }

    public function messages(): array
    {
        return [
            'estado_mat.required' => 'Seleccione el estado de la materia.',
            'estado_mat.in' => 'Seleccione un estado válido para la materia.',
        ];
    }
}
