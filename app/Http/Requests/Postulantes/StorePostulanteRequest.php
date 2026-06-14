<?php

namespace App\Http\Requests\Postulantes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostulanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('postulantes.crear') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombres_post' => ['required', 'string', 'max:120'],
            'apellidos_post' => ['required', 'string', 'max:120'],
            'ci_post' => ['nullable', 'string', 'max:30', 'unique:postulantes,ci_post'],
            'email_post' => ['nullable', 'email', 'max:255'],
            'celular_post' => ['nullable', 'string', 'max:30'],
            'edad_post' => ['nullable', 'integer', 'min:14', 'max:80'],
            'id_col' => ['nullable', 'integer', 'exists:colegios,id_col'],
            'id_uni' => ['nullable', 'integer', 'exists:universidades,id_uni'],
            'id_car' => [
                'nullable',
                'integer',
                Rule::exists('carreras', 'id_car')->where(
                    fn ($query) => $query->when(
                        $this->filled('id_uni'),
                        fn ($query) => $query->where('id_uni', $this->integer('id_uni'))
                    )
                ),
            ],
            'turno_post' => ['nullable', 'string', 'max:60'],
            'gestion_post' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'estado_post' => ['required', 'in:activo,inactivo'],
            'observaciones_post' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombres_post' => 'nombres',
            'apellidos_post' => 'apellidos',
            'ci_post' => 'C.I.',
            'email_post' => 'correo electrónico',
            'celular_post' => 'celular',
            'edad_post' => 'edad',
            'id_col' => 'colegio de procedencia',
            'id_uni' => 'universidad postulada',
            'id_car' => 'carrera postulada',
            'turno_post' => 'turno',
            'gestion_post' => 'gestión',
            'estado_post' => 'estado',
            'observaciones_post' => 'observaciones',
        ];
    }

    public function messages(): array
    {
        return [
            'nombres_post.required' => 'Los nombres del postulante son obligatorios.',
            'apellidos_post.required' => 'Los apellidos del postulante son obligatorios.',
            'ci_post.unique' => 'El C.I. ya está registrado para otro postulante.',
            'email_post.email' => 'Ingrese un correo electrónico válido.',
            'edad_post.integer' => 'La edad debe ser un número entero.',
            'edad_post.min' => 'La edad mínima permitida es 14 años.',
            'edad_post.max' => 'La edad máxima permitida es 80 años.',
            'id_col.exists' => 'El colegio de procedencia seleccionado no existe.',
            'id_uni.exists' => 'La universidad seleccionada no existe.',
            'id_car.exists' => 'La carrera seleccionada no pertenece a la universidad indicada.',
            'gestion_post.required' => 'La gestión es obligatoria.',
            'gestion_post.integer' => 'La gestión debe ser un año válido.',
            'estado_post.in' => 'Seleccione un estado válido para el postulante.',
        ];
    }
}
