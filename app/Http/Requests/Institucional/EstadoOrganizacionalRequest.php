<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Http\Requests\NormalizedFormRequest;
use Illuminate\Validation\Rule;

class EstadoOrganizacionalRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageOrganization($this->route('cargo') ? 'cargos.cambiar_estado' : 'personal.cambiar_estado') ?? false;
    }

    public function rules(): array
    {
        return ['estado' => ['required', Rule::enum($this->route('cargo') ? EstadoCargo::class : EstadoPersonal::class)]];
    }
}
