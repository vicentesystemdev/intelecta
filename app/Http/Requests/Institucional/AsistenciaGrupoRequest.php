<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsistenciaGrupoRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('asistencia.crear') ?? false;
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
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
            'fecha_asist' => ['required', 'date_format:Y-m-d', 'date', 'before_or_equal:today'],
            'sesion_asist' => ['required', 'string', 'min:2', 'max:120'],
            'registros' => ['required', 'array', 'list', 'min:1', 'max:'.InputRules::MAX_ATTENDANCE],
            'registros.*' => ['required', 'array:id_post,estado_asist,observacion_asist'],
            'registros.*.id_post' => ['required', 'integer', 'distinct', 'exists:postulantes,id_post'],
            'registros.*.estado_asist' => ['required', Rule::in(['presente', 'ausente', 'retraso', 'justificado'])],
            'registros.*.observacion_asist' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_grupo.required' => 'Seleccione un grupo o paralelo.',
            'id_grupo.exists' => 'El grupo seleccionado no existe.',
            'fecha_asist.required' => 'La fecha de asistencia es obligatoria.',
            'fecha_asist.date' => 'La fecha de asistencia no tiene un formato válido.',
            'fecha_asist.before_or_equal' => 'La asistencia no puede registrarse en una fecha futura.',
            'sesion_asist.required' => 'Indique la sesión académica.',
            'registros.required' => 'Debe incluir al menos un postulante para registrar la asistencia.',
            'registros.min' => 'Debe incluir al menos un postulante para registrar la asistencia.',
            'registros.*.estado_asist.in' => 'Seleccione un estado válido de asistencia.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $grupo = GrupoAcademico::find($this->integer('id_grupo'));
                if (! $grupo) {
                    return;
                }

                $ambito = app(AmbitoDocenteService::class);
                $user = $this->user();
                abort_unless($ambito->puedeVerGrupo($user, $grupo, 'asistencia.crear'), 403);
                if ($this->filled('id_tutor') && $ambito->esDocenteRestringido($user, 'asistencia.crear')) {
                    abort_unless($ambito->tutorId($user, 'asistencia.crear') === $this->integer('id_tutor'), 403);
                }

                if ($this->filled('id_prog') && $grupo->id_prog !== $this->integer('id_prog')) {
                    $validator->errors()->add('id_prog', 'El grupo seleccionado no pertenece al programa indicado.');
                }

                $postulantes = collect($this->input('registros', []))
                    ->pluck('id_post')
                    ->map(fn ($id) => (int) $id)
                    ->unique();
                if ($ambito->cantidadInscripcionesActivasPermitidas(
                    $user,
                    $grupo->id_grupo,
                    $postulantes,
                    'asistencia.crear',
                ) !== $postulantes->count()) {
                    abort(403, 'Uno o más postulantes no pertenecen al ámbito académico permitido.');
                }
            },
        ];
    }
}
