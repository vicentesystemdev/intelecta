<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\NormalizedFormRequest;
use Illuminate\Validation\Rule;

class UpdateRolPermisosRequest extends NormalizedFormRequest
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
        return [
            'permissions' => ['present', 'array', 'list', 'max:500'],
            'permissions.*' => [
                'string',
                'max:255',
                'distinct',
                Rule::exists('permissions', 'name')->where('guard_name', 'web'),
            ],
        ];
    }
}
