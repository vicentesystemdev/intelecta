<?php

namespace App\Http\Requests\Postulantes;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Domains\Postulantes\Support\BirthDate;
use App\Http\Requests\NormalizedFormRequest;
use App\Rules\PostulanteBirthDate;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class StorePostulanteRequest extends NormalizedFormRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($date = BirthDate::canonical($this->input('fecha_nacimiento_post'))) {
            $this->merge(['fecha_nacimiento_post' => $date]);
        }
    }

    protected function birthDateRules(): array
    {
        return ['required', 'string', new PostulanteBirthDate];
    }

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
            'nombres_post' => ['required', ...InputRules::person(120)],
            'apellidos_post' => ['required', ...InputRules::person(120)],
            'ci_post' => [
                'nullable',
                ...InputRules::document(), Rule::unique('postulantes', 'ci_post')->ignore($this->route('postulante')?->getKey(), 'id_post')],
            'email_post' => ['nullable', ...InputRules::email(254)],
            'celular_post' => ['nullable', ...InputRules::phone()],
            'fecha_nacimiento_post' => $this->birthDateRules(),
            // Old clients may still send this field; it is never a writable source of truth.
            'edad_post' => ['exclude'],
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
            'turno_post' => ['nullable', 'string', Rule::in(InputRules::options('turno_post'))],
            'gestion_post' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'estado_post' => ['required', Rule::enum(EstadoRegistro::class)],
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
            'fecha_nacimiento_post' => 'fecha de nacimiento',
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
            'fecha_nacimiento_post.required' => __('validation.birth_date_required'),
            'fecha_nacimiento_post.string' => __('validation.birth_date_format'),
            'id_col.exists' => 'El colegio de procedencia seleccionado no existe.',
            'id_uni.exists' => 'La universidad seleccionada no existe.',
            'id_car.exists' => 'La carrera seleccionada no pertenece a la universidad indicada.',
            'gestion_post.required' => 'La gestión es obligatoria.',
            'gestion_post.integer' => 'La gestión debe ser un año válido.',
            'estado_post.required' => 'Seleccione el estado del postulante.',
            'estado_post.in' => 'Seleccione un estado válido para el postulante.',
        ];
    }
}
