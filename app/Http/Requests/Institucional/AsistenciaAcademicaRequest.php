<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsistenciaAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sesion_asist' => trim((string) $this->input('sesion_asist')) ?: 'General',
        ]);
    }

    public function rules(): array
    {
        $asistencia = $this->route('asistencia');

        return [
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['required', 'integer', 'exists:grupos_academicos,id_grupo'],
            'id_post' => [
                'required',
                'integer',
                'exists:postulantes,id_post',
                Rule::unique('asistencias_academicas', 'id_post')
                    ->where(fn ($query) => $query
                        ->where('id_grupo', $this->input('id_grupo'))
                        ->where('fecha_asist', $this->input('fecha_asist'))
                        ->where('sesion_asist', $this->input('sesion_asist')))
                    ->ignore($asistencia?->id_asist, 'id_asist'),
            ],
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
            'fecha_asist' => ['required', 'date'],
            'sesion_asist' => ['required', 'string', 'max:120'],
            'estado_asist' => ['required', Rule::in(['presente', 'ausente', 'retraso', 'justificado'])],
            'observacion_asist' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $grupo = GrupoAcademico::find($this->integer('id_grupo'));

                if ($grupo && $this->filled('id_prog') && $grupo->id_prog !== $this->integer('id_prog')) {
                    $validator->errors()->add('id_prog', 'El grupo seleccionado no pertenece al programa indicado.');
                }

                if (
                    $grupo
                    && $this->filled('id_post')
                    && ! InscripcionAcademica::query()
                        ->where('id_grupo', $grupo->id_grupo)
                        ->where('id_post', $this->integer('id_post'))
                        ->where('estado_inscripcion', 'activo')
                        ->exists()
                ) {
                    $validator->errors()->add('id_post', 'El postulante no tiene una inscripción activa en el grupo seleccionado.');
                }
            },
        ];
    }
}
