<?php

namespace App\Domains\Seguridad\Services;

use App\Domains\Seguridad\Support\MatrizRbac;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermisosRolService
{
    public function update(User $actor, int $roleId, array $permissions): void
    {
        app(CuentaService::class)->transaction(function () use ($actor, $roleId, $permissions): void {
            $actor = User::findOrFail($actor->id);
            abort_unless($actor->canChangeLoginEmail(), 403);
            $role = Role::query()->lockForUpdate()->findOrFail($roleId);
            if ($role->guard_name !== 'web' || ! in_array($role->name, MatrizRbac::ROLES, true) || $role->name === 'Super Administrador') {
                throw ValidationException::withMessages(['permissions' => 'El rol está protegido o no pertenece a los roles funcionales web.']);
            }
            Validator::make(['permissions' => $permissions], [
                'permissions' => ['bail', 'array', 'list'],
                'permissions.*' => ['bail', 'string', 'distinct:strict', Rule::in(MatrizRbac::forRole($role->name)), Rule::exists('permissions', 'name')->where('guard_name', 'web')],
            ])->validate();
            $before = $role->permissions()->pluck('name')->sort()->values()->all();
            sort($permissions);
            if ($before === $permissions) {
                return;
            }
            $role->syncPermissions($permissions);
            app(BitacoraService::class)->registrar([
                'user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
                'rol_usuario' => $actor->rolesLabel(), 'accion' => 'actualizar_permisos', 'modulo' => 'Roles y Permisos',
                'entidad' => 'roles', 'entidad_id' => $role->id, 'descripcion' => 'Cambio de matriz: '.$role->name,
                'valores_anteriores' => ['rol' => $role->name, 'permissions' => $before],
                'valores_nuevos' => ['rol' => $role->name, 'permissions' => $permissions,
                    'agregados' => array_values(array_diff($permissions, $before)), 'retirados' => array_values(array_diff($before, $permissions))],
                'severidad' => 'seguridad',
            ]);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
