<?php

namespace App\Http\Requests\Institucional;

use App\Http\Requests\NormalizedFormRequest;
use Illuminate\Validation\Rule;

class MatriculaAcademicaRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'matriculas-cuotas.crear'
            : 'matriculas-cuotas.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $matricula = $this->route('matricula');

        return [
            'id_post' => ['prohibited'],
            'id_prog' => ['prohibited'],
            'id_grupo' => ['prohibited'],
            'id_insc' => [
                'required',
                'integer',
                'exists:inscripciones_academicas,id_insc',
                Rule::unique('matriculas_academicas', 'id_insc')
                    ->ignore($matricula?->id_mat, 'id_mat'),
            ],
            'codigo_mat' => ['prohibited'],
            'fecha_matricula_mat' => ['prohibited'],
            'monto_matricula_mat' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'estado_matricula_mat' => ['required', Rule::in(['activa', 'observada', 'inactiva', 'becada', 'exenta'])],
            'tipo_beneficio_mat' => ['nullable', 'string', 'max:120'],
            'observacion_mat' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_post.prohibited' => 'El postulante se obtiene de la inscripción y no puede enviarse manualmente.',
            'id_prog.prohibited' => 'El programa se obtiene de la inscripción y no puede enviarse manualmente.',
            'id_grupo.prohibited' => 'El grupo se obtiene de la inscripción y no puede enviarse manualmente.',
            'id_insc.required' => 'Seleccione una inscripción académica.',
            'id_insc.exists' => 'La inscripción académica seleccionada no existe.',
            'id_insc.unique' => 'La inscripción ya tiene una matrícula académica registrada.',
            'codigo_mat.prohibited' => 'El código de matrícula se genera automáticamente y no puede enviarse manualmente.',
            'fecha_matricula_mat.prohibited' => 'La fecha de matrícula se asigna automáticamente por el servidor y no puede enviarse manualmente.',
            'monto_matricula_mat.required' => 'El monto de matrícula es obligatorio.',
            'monto_matricula_mat.numeric' => 'El monto de matrícula debe ser numérico.',
            'monto_matricula_mat.min' => 'El monto de matrícula no puede ser negativo.',
            'estado_matricula_mat.required' => 'Seleccione el estado de la matrícula académica.',
            'estado_matricula_mat.in' => 'Seleccione un estado válido para la matrícula.',
        ];
    }
}
