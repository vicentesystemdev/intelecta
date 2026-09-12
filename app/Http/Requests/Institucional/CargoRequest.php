<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Institucional\Models\Cargo;
use App\Http\Requests\NormalizedFormRequest;
use App\Support\Validation\InputNormalizer;
use App\Support\Validation\InputRules;

class CargoRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageOrganization($this->isMethod('post') ? 'cargos.crear' : 'cargos.editar') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_cargo' => ['required', ...InputRules::denomination(), function ($attribute, $value, $fail): void {
                if (Cargo::query()
                    ->where('id_cargo', '<>', $this->route('cargo')?->id_cargo ?? 0)
                    ->get(['nombre_cargo'])
                    ->contains(fn (Cargo $cargo) => InputNormalizer::key($cargo->nombre_cargo) === InputNormalizer::key((string) $value))) {
                    $fail('Ya existe un cargo con esa denominación.');
                }
            }],
            'descripcion' => ['required', ...InputRules::descriptiveText(10, 2000)],
            'estado' => ['prohibited'],
            'role_id' => ['prohibited'],
            'permission_id' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cargo.required' => 'El nombre del cargo es obligatorio.',
            'descripcion.required' => 'La descripción del cargo es obligatoria.',
            'descripcion.min' => 'La descripción debe contener al menos 10 caracteres.',
        ];
    }
}
