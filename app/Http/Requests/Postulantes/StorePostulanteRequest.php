<?php

namespace App\Http\Requests\Postulantes;

use App\Domains\Academico\Enums\EstadoRegistro;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Postulantes\Support\BirthDate;
use App\Http\Requests\NormalizedFormRequest;
use App\Rules\PostulanteBirthDate;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        $rawUniversidadId = $this->input('id_uni');
        $universidadId = ! is_bool($rawUniversidadId)
            && is_scalar($rawUniversidadId)
            && ctype_digit((string) $rawUniversidadId)
            ? (int) $rawUniversidadId
            : null;

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
            'crear_otro_colegio' => ['sometimes', 'boolean'],
            'otro_colegio_nombre' => [Rule::requiredIf($this->boolean('crear_otro_colegio')), ...InputRules::denomination(255)],
            'id_col' => $this->boolean('crear_otro_colegio')
                ? ['prohibited']
                : ['nullable', 'integer', 'exists:colegios,id_col'],
            'crear_otra_universidad' => ['sometimes', 'boolean'],
            'otra_universidad_nombre' => [Rule::requiredIf($this->boolean('crear_otra_universidad')), ...InputRules::denomination(255)],
            'otra_universidad_sigla' => ['nullable', ...InputRules::code(60)],
            'id_uni' => $this->boolean('crear_otra_universidad')
                ? ['prohibited']
                : ['nullable', 'integer', 'exists:universidades,id_uni'],
            'crear_otra_carrera' => ['sometimes', 'boolean'],
            'otra_carrera_nombre' => [Rule::requiredIf($this->boolean('crear_otra_carrera')), ...InputRules::denomination(255)],
            'id_car' => $this->boolean('crear_otra_carrera') ? ['prohibited'] : [
                'nullable',
                'integer',
                Rule::exists('carreras', 'id_car')->where(
                    fn ($query) => $query->when(
                        $universidadId !== null,
                        fn ($query) => $query->where('id_uni', $universidadId)
                    )
                ),
            ],
            'turno_post' => ['nullable', 'string', Rule::in(InputRules::options('turno_post'))],
            'gestion_post' => ['required', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'estado_post' => ['required', Rule::enum(EstadoRegistro::class)],
            'observaciones_post' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $universidadIndicada = $this->filled('id_uni') || $this->boolean('crear_otra_universidad');
            $carreraIndicada = $this->filled('id_car') || $this->boolean('crear_otra_carrera');
            if ($universidadIndicada && ! $carreraIndicada) {
                $validator->errors()->add('id_car', 'Seleccione o registre una carrera para la universidad postulada.');
            }
            if ($this->boolean('crear_otra_carrera') && ! $this->filled('id_uni') && ! $this->boolean('crear_otra_universidad')) {
                $validator->errors()->add('id_uni', 'Seleccione o registre la universidad a la que pertenece la nueva carrera.');
            }
            if ($this->boolean('crear_otra_universidad') && $this->filled('id_car') && ! $this->boolean('crear_otra_carrera')) {
                $validator->errors()->add('id_car', 'Seleccione “Otra carrera” para la nueva universidad.');
            }
            if ($this->filled('id_car') && $this->filled('id_uni') && ! Carrera::query()
                ->whereKey($this->integer('id_car'))
                ->where('id_uni', $this->integer('id_uni'))
                ->exists()) {
                $validator->errors()->add('id_car', 'La carrera seleccionada no pertenece a la universidad indicada.');
            }
        }];
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
            'id_col.prohibited' => 'No envíe un colegio existente cuando registre otro colegio.',
            'otro_colegio_nombre.required' => 'Ingrese el nombre del nuevo colegio.',
            'id_uni.exists' => 'La universidad seleccionada no existe.',
            'id_uni.prohibited' => 'No envíe una universidad existente cuando registre otra universidad.',
            'otra_universidad_nombre.required' => 'Ingrese el nombre de la nueva universidad.',
            'id_car.exists' => 'La carrera seleccionada no pertenece a la universidad indicada.',
            'id_car.prohibited' => 'No envíe una carrera existente cuando registre otra carrera.',
            'otra_carrera_nombre.required' => 'Ingrese el nombre de la nueva carrera.',
            'gestion_post.required' => 'La gestión es obligatoria.',
            'gestion_post.integer' => 'La gestión debe ser un año válido.',
            'estado_post.required' => 'Seleccione el estado del postulante.',
            'estado_post.enum' => 'Seleccione un estado válido para el postulante.',
        ];
    }
}
