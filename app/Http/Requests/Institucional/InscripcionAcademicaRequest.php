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
            'fecha_inscripcion' => ['nullable', 'date'],
            'estado_inscripcion' => ['required', 'in:activo,inactivo'],
            'observacion_inscripcion' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
