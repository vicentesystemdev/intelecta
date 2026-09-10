<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\NormalizedFormRequest;
use App\Models\User;
use App\Support\Validation\InputRules;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends NormalizedFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canChangeLoginEmail() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $usuario */
        $usuario = $this->route('usuario');

        return [
            'name' => ['required', ...InputRules::person(255)],
            'email' => [
                'required',
                ...InputRules::email(),
                Rule::unique('users', 'email')->ignore($usuario->getKey()),
            ],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'estado_cuenta' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'role' => ['missing'],
            'roles' => ['missing'],
        ];
    }
}
