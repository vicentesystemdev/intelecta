<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InscripcionAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'fecha_inscripcion' => ['nullable', 'date', 'before_or_equal:today'],
            'estado_inscripcion' => ['required', 'in:activo,inactivo'],
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
            'fecha_inscripcion.date' => 'La fecha de inscripción no tiene un formato válido.',
            'fecha_inscripcion.before_or_equal' => 'La fecha de inscripción no puede ser posterior a la fecha actual.',
            'estado_inscripcion.required' => 'Seleccione el estado de la inscripción académica.',
            'estado_inscripcion.in' => 'Seleccione un estado válido para la inscripción.',
        ];
    }
}
