<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\AcademicDatePolicy;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsignacionTutorRequest extends NormalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $asignacion = $this->route('asignacion');
        if ($asignacion) {
            $this->merge([
                ...(! $this->exists('fecha_inicio_asig') ? ['fecha_inicio_asig' => $asignacion->fecha_inicio_asig?->format('Y-m-d')] : []),
                ...(! $this->exists('fecha_fin_asig') ? ['fecha_fin_asig' => $asignacion->fecha_fin_asig?->format('Y-m-d')] : []),
            ]);
        }
    }

    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'asignaciones-tutores.crear'
            : 'asignaciones-tutores.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $asignacion = $this->route('asignacion');

        return [
            'id_tutor' => ['required', 'integer', 'exists:tutores_academicos,id_tutor'],
            'id_prog' => [
                'nullable',
                'required_without:id_grupo',
                'integer',
                'exists:programas_academicos,id_prog',
            ],
            'id_grupo' => [
                'nullable',
                'required_without:id_prog',
                'integer',
                'exists:grupos_academicos,id_grupo',
            ],
            'materia_referencia_asig' => ['nullable', 'string', 'max:160'],
            'rol_asig' => ['nullable', 'string', 'max:120'],
            'fecha_inicio_asig' => [
                $asignacion ? 'nullable' : 'required',
                'date_format:Y-m-d',
            ],
            'fecha_fin_asig' => [$asignacion ? 'nullable' : 'required', 'date_format:Y-m-d'],
            'estado_asig' => ['required', Rule::enum(EstadoRegistro::class)],
            'observacion_asig' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $asignacion = $this->route('asignacion');
                $inicio = $this->input('fecha_inicio_asig');
                $fin = $this->input('fecha_fin_asig');
                $inicioAnterior = $asignacion?->fecha_inicio_asig?->format('Y-m-d');
                $finAnterior = $asignacion?->fecha_fin_asig?->format('Y-m-d');
                $reprograma = ! $asignacion || $inicio !== $inicioAnterior || $fin !== $finAnterior;

                if ($reprograma) {
                    if (! $inicio || ! AcademicDatePolicy::isTomorrowOrLater($inicio)) {
                        $validator->errors()->add('fecha_inicio_asig', 'La fecha de inicio debe ser posterior a la fecha actual.');
                    }
                    if (! $fin || ($inicio && ! AcademicDatePolicy::isStrictlyAfter($fin, $inicio))) {
                        $validator->errors()->add('fecha_fin_asig', 'La fecha de finalización debe ser posterior a la fecha de inicio.');
                    }
                }

                if (! $this->filled('id_prog') || ! $this->filled('id_grupo')) {
                    return;
                }

                $belongsToProgram = GrupoAcademico::query()
                    ->where('id_grupo', $this->integer('id_grupo'))
                    ->where('id_prog', $this->integer('id_prog'))
                    ->exists();

                if (! $belongsToProgram) {
                    $validator->errors()->add(
                        'id_grupo',
                        'El grupo seleccionado no pertenece al programa académico indicado.',
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'id_tutor.required' => 'Seleccione un tutor académico.',
            'id_tutor.exists' => 'El tutor académico seleccionado no existe.',
            'id_prog.required_without' => 'Seleccione un programa académico o un grupo.',
            'id_grupo.required_without' => 'Seleccione un grupo o un programa académico.',
            'fecha_inicio_asig.date_format' => 'La fecha de inicio no tiene un formato válido.',
            'fecha_inicio_asig.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin_asig.date_format' => 'La fecha de finalización no tiene un formato válido.',
            'fecha_fin_asig.required' => 'La fecha de finalización es obligatoria.',
            'estado_asig.required' => 'Seleccione el estado de la asignación tutorial.',
            'estado_asig.enum' => 'Seleccione un estado válido para la asignación tutorial.',
        ];
    }
}
