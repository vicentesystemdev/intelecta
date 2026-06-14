<?php

namespace App\Http\Requests\Institucional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ProgramaAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post')
            ? 'programas.crear'
            : 'programas.editar';

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $programa = $this->route('programa');

        return [
            'nombre_prog' => ['required', 'string', 'max:180'],
            'codigo_prog' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('programas_academicos', 'codigo_prog')
                    ->ignore($programa?->id_prog, 'id_prog'),
            ],
            'universidad_objetivo_prog' => ['nullable', 'string', 'max:180'],
            'carrera_area_prog' => ['nullable', 'string', 'max:180'],
            'modalidad_prog' => ['nullable', 'string', 'max:100'],
            'fecha_inicio_prog' => [
                'bail',
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($programa): void {
                    if (! $value) {
                        return;
                    }

                    $existingDate = $programa?->fecha_inicio_prog?->format('Y-m-d');
                    $isHistoricalDateUnchanged = $existingDate && $existingDate === $value;

                    if (Carbon::parse($value)->startOfDay()->lt(today()) && ! $isHistoricalDateUnchanged) {
                        $fail('La fecha de inicio de un programa nuevo no puede ser anterior a la fecha actual.');
                    }
                },
            ],
            'fecha_fin_prog' => ['nullable', 'date', 'after_or_equal:fecha_inicio_prog'],
            'descripcion_prog' => ['nullable', 'string', 'max:3000'],
            'estado_prog' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_prog' => 'nombre del programa',
            'codigo_prog' => 'código',
            'universidad_objetivo_prog' => 'universidad objetivo',
            'carrera_area_prog' => 'carrera o área',
            'modalidad_prog' => 'modalidad',
            'fecha_inicio_prog' => 'fecha de inicio',
            'fecha_fin_prog' => 'fecha de finalización',
            'descripcion_prog' => 'descripción',
            'estado_prog' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_prog.required' => 'El nombre del programa académico es obligatorio.',
            'codigo_prog.unique' => 'El código indicado ya pertenece a otro programa académico.',
            'fecha_inicio_prog.date' => 'La fecha de inicio no tiene un formato válido.',
            'fecha_fin_prog.date' => 'La fecha de finalización no tiene un formato válido.',
            'fecha_fin_prog.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio.',
            'estado_prog.required' => 'Seleccione el estado del programa académico.',
            'estado_prog.in' => 'Seleccione un estado válido para el programa académico.',
        ];
    }
}
