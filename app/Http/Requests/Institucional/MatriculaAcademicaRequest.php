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
            'fecha_matricula_mat' => ['nullable', 'date'],
            'monto_matricula_mat' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'estado_matricula_mat' => ['required', Rule::in(['activa', 'observada', 'inactiva', 'becada', 'exenta'])],
            'tipo_beneficio_mat' => ['nullable', 'string', 'max:120'],
            'observacion_mat' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
