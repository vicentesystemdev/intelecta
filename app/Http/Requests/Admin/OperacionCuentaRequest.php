<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\NormalizedFormRequest;

class OperacionCuentaRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canChangeLoginEmail() ?? false;
    }

    public function rules(): array
    {
        return ['motivo' => [$this->routeIs('admin.sistema.usuarios.bloquear') ? 'required' : 'nullable', 'string', 'min:10', 'max:500']];
    }
}
