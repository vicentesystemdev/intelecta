<?php

namespace App\Http\Requests\Evaluaciones;

use App\Domains\Evaluaciones\Models\Materia;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputNormalizer;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class MateriaRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post') ? 'materias.crear' : 'materias.editar';

        return $this->user()?->cuentaActiva()
            && $this->user()->hasRole('Super Administrador')
            && $this->user()->can($permission);
    }

    public function rules(): array
    {
        $materia = $this->route('materia');

        return [
            'codigo_mat' => ['required', ...InputRules::code(60), Rule::unique('materias', 'codigo_mat')->ignore($materia?->id_mat, 'id_mat')],
            'nombre_mat' => ['required', ...InputRules::denomination(255), function (string $attribute, mixed $value, \Closure $fail) use ($materia): void {
                $duplicate = Materia::query()
                    ->where('id_mat', '<>', $materia?->id_mat ?? 0)
                    ->get(['nombre_mat'])
                    ->contains(fn (Materia $item) => InputNormalizer::key($item->nombre_mat) === InputNormalizer::key((string) $value));
                if ($duplicate) {
                    $fail('Ya existe una materia con esa denominación.');
                }
            }],
            'descripcion_mat' => ['required', ...InputRules::descriptiveText(10, 3000)],
            'estado_mat' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_mat.required' => 'El código de la materia es obligatorio.',
            'codigo_mat.unique' => 'El código ya pertenece a otra materia.',
            'nombre_mat.required' => 'El nombre de la materia es obligatorio.',
            'descripcion_mat.required' => 'La descripción de la materia es obligatoria.',
            'descripcion_mat.min' => 'La descripción debe contener al menos 10 caracteres.',
            'estado_mat.prohibited' => 'El estado se gestiona mediante su acción específica.',
        ];
    }
}
