<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Enums\EstadoSimulacro;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\AcademicDatePolicy;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SimulacroProgramadoRequest extends NormalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $simulacro = $this->route('simulacro');
        if ($simulacro) {
            $this->merge([
                ...(! $this->exists('fecha_sim') ? ['fecha_sim' => $simulacro->fecha_sim?->format('Y-m-d')] : []),
                ...(! $this->exists('hora_inicio_sim') ? ['hora_inicio_sim' => self::normalizeTime($simulacro->hora_inicio_sim)] : []),
                ...(! $this->exists('hora_fin_sim') ? ['hora_fin_sim' => self::normalizeTime($simulacro->hora_fin_sim)] : []),
            ]);
        }
    }

    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'simulacros.crear'
            : 'simulacros.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $simulacro = $this->route('simulacro');

        return [
            'id_prog' => ['required', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => [
                'nullable',
                'integer',
                Rule::exists('grupos_academicos', 'id_grupo')
                    ->where(fn ($query) => $query->where('id_prog', $this->integer('id_prog'))),
            ],
            'id_plantilla' => ['nullable', 'integer', 'exists:plantillas_evaluacion,id_plan'],
            'titulo_sim' => ['required', 'string', 'min:2', 'max:180'],
            'fecha_sim' => [
                $simulacro ? 'nullable' : 'required',
                'date_format:Y-m-d',
            ],
            'hora_inicio_sim' => [$simulacro ? 'nullable' : 'required', 'date_format:H:i'],
            'hora_fin_sim' => [$simulacro ? 'nullable' : 'required', 'date_format:H:i', ...InputRules::dateOrder('after:hora_inicio_sim', $this->input('hora_inicio_sim'), 'H:i')],
            'modalidad_sim' => ['nullable', 'string', 'max:100'],
            'estado_sim' => ['required', Rule::enum(EstadoSimulacro::class)],
            'observacion_sim' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('fecha_sim')) {
                return;
            }
            $simulacro = $this->route('simulacro');
            $fecha = $this->input('fecha_sim');
            $horaInicio = self::normalizeTime($this->input('hora_inicio_sim'));
            $horaFin = self::normalizeTime($this->input('hora_fin_sim'));
            $reprograma = ! $simulacro
                || $fecha !== $simulacro->fecha_sim?->format('Y-m-d')
                || $horaInicio !== self::normalizeTime($simulacro->hora_inicio_sim)
                || $horaFin !== self::normalizeTime($simulacro->hora_fin_sim);
            if ($reprograma && (! $fecha || ! AcademicDatePolicy::isTomorrowOrLater($fecha))) {
                $validator->errors()->add('fecha_sim', 'El simulacro debe programarse al menos con un día de anticipación.');
            }
            if ($simulacro && $reprograma && $horaInicio === null) {
                $validator->errors()->add('hora_inicio_sim', 'La hora de inicio es obligatoria al reprogramar el simulacro.');
            }
            if ($simulacro && $reprograma && $horaFin === null) {
                $validator->errors()->add('hora_fin_sim', 'La hora de finalización es obligatoria al reprogramar el simulacro.');
            }
        }];
    }

    private static function normalizeTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    public function messages(): array
    {
        return [
            'id_prog.required' => 'Seleccione un programa académico.',
            'id_prog.exists' => 'El programa académico seleccionado no existe.',
            'id_grupo.exists' => 'El grupo seleccionado no pertenece al programa académico.',
            'id_plantilla.exists' => 'La plantilla de evaluación seleccionada no existe.',
            'titulo_sim.required' => 'El título del simulacro es obligatorio.',
            'fecha_sim.date_format' => 'La fecha del simulacro no tiene un formato válido.',
            'fecha_sim.required' => 'La fecha del simulacro es obligatoria.',
            'hora_inicio_sim.required' => 'La hora de inicio es obligatoria.',
            'hora_fin_sim.required' => 'La hora de finalización es obligatoria.',
            'hora_inicio_sim.date_format' => 'La hora de inicio no tiene un formato válido.',
            'hora_fin_sim.date_format' => 'La hora de finalización no tiene un formato válido.',
            'hora_fin_sim.after' => 'La hora de finalización debe ser posterior a la hora de inicio.',
            'estado_sim.required' => 'Seleccione el estado del simulacro programado.',
            'estado_sim.enum' => 'Seleccione un estado válido para el simulacro.',
        ];
    }
}
