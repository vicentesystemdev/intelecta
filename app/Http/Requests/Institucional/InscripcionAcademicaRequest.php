<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Http\Requests\NormalizedFormRequest;
use Illuminate\Validation\Rule;

class InscripcionAcademicaRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'inscripciones.crear'
            : 'inscripciones.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $inscripcion = $this->route('inscripcion');

        return [
            'id_prog' => ['required', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => [
                'nullable',
                'integer',
                Rule::exists('grupos_academicos', 'id_grupo')
                    ->where(fn ($query) => $query->where('id_prog', $this->integer('id_prog'))),
            ],
            'id_post' => [
                'required',
                'integer',
                'exists:postulantes,id_post',
                Rule::unique('inscripciones_academicas', 'id_post')
                    ->where(fn ($query) => $query->where('id_prog', $this->integer('id_prog')))
                    ->ignore($inscripcion?->id_insc, 'id_insc'),
            ],
            'fecha_inscripcion' => ['prohibited'],
            'estado_inscripcion' => $this->isMethod('post')
                ? ['prohibited']
                : ['required', Rule::enum(EstadoRegistro::class)],
            'observacion_inscripcion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_prog.required' => 'Seleccione un programa académico.',
            'id_prog.exists' => 'El programa académico seleccionado no existe.',
            'id_grupo.exists' => 'El grupo seleccionado no pertenece al programa académico.',
            'id_post.required' => 'Seleccione un postulante.',
            'id_post.exists' => 'El postulante seleccionado no existe.',
            'id_post.unique' => 'El postulante ya está inscrito en este programa académico.',
            'fecha_inscripcion.prohibited' => 'La fecha de inscripción se asigna automáticamente por el servidor y no puede enviarse manualmente.',
            'estado_inscripcion.prohibited' => 'Una nueva inscripción nace activa; el estado no puede seleccionarse durante el alta.',
            'estado_inscripcion.required' => 'Seleccione el estado de la inscripción académica.',
            'estado_inscripcion.enum' => 'Seleccione un estado válido para la inscripción.',
        ];
    }
}
