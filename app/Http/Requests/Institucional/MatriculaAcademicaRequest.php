<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatriculaAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $matricula = $this->route('matricula');

        return [
            'id_insc' => [
                'required',
                'integer',
                'exists:inscripciones_academicas,id_insc',
                Rule::unique('matriculas_academicas', 'id_insc')
                    ->ignore($matricula?->id_mat, 'id_mat'),
            ],
            'codigo_mat' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('matriculas_academicas', 'codigo_mat')
                    ->ignore($matricula?->id_mat, 'id_mat'),
            ],
            'fecha_matricula_mat' => ['nullable', 'date', 'before_or_equal:today'],
            'monto_matricula_mat' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'estado_matricula_mat' => ['required', Rule::in(['activa', 'observada', 'inactiva', 'becada', 'exenta'])],
            'tipo_beneficio_mat' => ['nullable', 'string', 'max:120'],
            'observacion_mat' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_insc.required' => 'Seleccione una inscripción académica.',
            'id_insc.exists' => 'La inscripción académica seleccionada no existe.',
            'id_insc.unique' => 'La inscripción ya tiene una matrícula académica registrada.',
            'codigo_mat.unique' => 'El código de matrícula ya está registrado.',
            'fecha_matricula_mat.date' => 'La fecha de matrícula no tiene un formato válido.',
            'fecha_matricula_mat.before_or_equal' => 'La fecha de matrícula no puede ser posterior a la fecha actual.',
            'monto_matricula_mat.required' => 'El monto de matrícula es obligatorio.',
            'monto_matricula_mat.numeric' => 'El monto de matrícula debe ser numérico.',
            'monto_matricula_mat.min' => 'El monto de matrícula no puede ser negativo.',
            'estado_matricula_mat.required' => 'Seleccione el estado de la matrícula académica.',
            'estado_matricula_mat.in' => 'Seleccione un estado válido para la matrícula.',
        ];
    }
}
