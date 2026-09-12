<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class GrupoAcademicoRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'grupos.crear'
            : 'grupos.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $grupo = $this->route('grupo');
        $changed = fn (string $field): bool => ! $grupo || (string) ($grupo->{$field} ?? '') !== (string) ($this->input($field) ?? '');

        return [
            'id_prog' => ['required', 'integer', 'exists:programas_academicos,id_prog'],
            'nombre_grupo' => ['required', ...InputRules::denomination(160)],
            'codigo_grupo' => [
                $grupo && blank($grupo->codigo_grupo) && blank($this->input('codigo_grupo')) ? 'nullable' : 'required',
                ...InputRules::code(),
                Rule::unique('grupos_academicos', 'codigo_grupo')
                    ->where(fn ($query) => $query->where('id_prog', $this->integer('id_prog')))
                    ->ignore($grupo?->id_grupo, 'id_grupo'),
            ],
            'turno_grupo' => [
                $grupo && blank($grupo->turno_grupo) && blank($this->input('turno_grupo')) ? 'nullable' : 'required',
                'string',
                'max:80',
                function (string $attribute, mixed $value, \Closure $fail) use ($changed): void {
                    if ($changed('turno_grupo') && ! in_array($value, ['Mañana', 'Tarde', 'Noche', 'Fin de Semana'], true)) {
                        $fail('Seleccione un turno académico válido.');
                    }
                },
            ],
            'aula_grupo' => [
                $grupo && blank($grupo->aula_grupo) && blank($this->input('aula_grupo')) ? 'nullable' : 'required',
                'string',
                'max:80',
                function (string $attribute, mixed $value, \Closure $fail) use ($changed): void {
                    if ($changed('aula_grupo') && ! preg_match('/^[0-9]+$/', (string) $value)) {
                        $fail('El aula debe contener únicamente números.');
                    }
                },
            ],
            'capacidad_grupo' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) use ($changed): void {
                    if ($changed('capacidad_grupo') && (int) $value > 20) {
                        $fail('La capacidad máxima permitida es de 20 estudiantes.');
                    }
                },
            ],
            'nivel_grupo' => [
                $grupo && blank($grupo->nivel_grupo) && blank($this->input('nivel_grupo')) ? 'nullable' : 'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($changed): void {
                    if ($changed('nivel_grupo') && $value !== 'Preuniversitario') {
                        $fail('Seleccione un nivel académico válido.');
                    }
                },
            ],
            'id_tutor_responsable' => [
                'nullable',
                'integer',
                Rule::exists('tutores_academicos', 'id_tutor')->where(fn ($query) => $query
                    ->where('estado_tutor', 'activo')
                    ->whereNull('deleted_at')),
            ],
            'tutor_responsable_grupo' => ['prohibited'],
            'estado_grupo' => ['required', Rule::enum(EstadoRegistro::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'id_prog.required' => 'Seleccione un programa académico.',
            'id_prog.exists' => 'El programa académico seleccionado no existe.',
            'nombre_grupo.required' => 'El nombre del grupo o paralelo es obligatorio.',
            'codigo_grupo.required' => 'El código del grupo es obligatorio.',
            'codigo_grupo.unique' => 'El código ya está registrado en este programa académico.',
            'capacidad_grupo.required' => 'La capacidad del grupo es obligatoria.',
            'capacidad_grupo.integer' => 'La capacidad debe ser un número entero.',
            'capacidad_grupo.min' => 'La capacidad debe ser mayor a cero.',
            'nivel_grupo.required' => 'Seleccione un nivel académico.',
            'turno_grupo.required' => 'Seleccione un turno académico.',
            'aula_grupo.required' => 'Indique el número de aula.',
            'id_tutor_responsable.exists' => 'Seleccione un tutor académico activo y elegible.',
            'estado_grupo.required' => 'Seleccione el estado del grupo académico.',
            'estado_grupo.enum' => 'Seleccione un estado válido para el grupo.',
        ];
    }
}
