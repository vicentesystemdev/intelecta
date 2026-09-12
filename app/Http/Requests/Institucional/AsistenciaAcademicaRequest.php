<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Http\Requests\NormalizedFormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsistenciaAcademicaRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'asistencia.crear'
            : 'asistencia.editar';

        $user = $this->user();
        if (! $user?->can($permission)) {
            return false;
        }

        $attendance = $this->route('asistencia');

        return ! $attendance || Gate::forUser($user)->allows('update', $attendance);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'sesion_asist' => $this->input('sesion_asist') ?? 'General',
        ]);
    }

    public function rules(): array
    {
        return [
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['required', 'integer', 'exists:grupos_academicos,id_grupo'],
            'id_post' => [
                'required',
                'integer',
                'exists:postulantes,id_post',
            ],
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
            'fecha_asist' => ['prohibited'],
            'sesion_asist' => ['required', 'string', 'min:2', 'max:120'],
            'estado_asist' => ['required', Rule::in(['presente', 'ausente', 'retraso', 'justificado'])],
            'observacion_asist' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_grupo.required' => 'Seleccione un grupo o paralelo.',
            'id_grupo.exists' => 'El grupo seleccionado no existe.',
            'id_post.required' => 'Seleccione un postulante.',
            'id_post.exists' => 'El postulante seleccionado no existe.',
            'id_post.unique' => 'La asistencia del postulante ya fue registrada para este grupo, fecha y sesión.',
            'id_tutor.exists' => 'El tutor académico seleccionado no existe.',
            'fecha_asist.prohibited' => 'La fecha de asistencia se asigna automáticamente por el servidor y no puede enviarse manualmente.',
            'sesion_asist.required' => 'Indique la sesión académica.',
            'estado_asist.required' => 'Seleccione el estado de asistencia del postulante.',
            'estado_asist.in' => 'Seleccione un estado válido de asistencia.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $ambito = app(AmbitoDocenteService::class);
                $user = $this->user();
                $capacidad = $this->isMethod('post') ? 'asistencia.crear' : 'asistencia.editar';
                abort_unless($ambito->puedeVerGrupo($user, $this->integer('id_grupo'), $capacidad), 403);
                abort_unless(
                    $ambito->inscripcionActivaPermitida(
                        $user,
                        $this->integer('id_grupo'),
                        $this->integer('id_post'),
                        $capacidad,
                    ),
                    403,
                );
                if ($this->filled('id_tutor') && $ambito->esDocenteRestringido($user, $capacidad)) {
                    abort_unless($ambito->tutorId($user, $capacidad) === $this->integer('id_tutor'), 403);
                }

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
