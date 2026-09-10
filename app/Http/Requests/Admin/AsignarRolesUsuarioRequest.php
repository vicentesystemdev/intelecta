<?php

namespace App\Http\Requests\Admin;

use App\Domains\Seguridad\Support\MatrizRbac;
use App\Http\Requests\NormalizedFormRequest;
use Illuminate\Validation\Rule;

class AsignarRolesUsuarioRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canChangeLoginEmail() ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['missing'],
            'roles' => ['present', 'array', 'list', 'max:4'],
            'roles.*' => ['string', 'distinct:strict', Rule::in(MatrizRbac::ROLES), Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }
}
