<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Institucional\Models\Cargo;
use App\Http\Requests\NormalizedFormRequest;
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
                if (Cargo::whereRaw('LOWER(nombre_cargo) = LOWER(?)', [$value])
                    ->where('id_cargo', '<>', $this->route('cargo')?->id_cargo ?? 0)->exists()) {
                    $fail('Ya existe un cargo con esa denominación.');
                }
            }],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'estado' => ['prohibited'],
            'role_id' => ['prohibited'],
            'permission_id' => ['prohibited'],
        ];
    }
}
