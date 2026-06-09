<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRolPermisosRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolPermisoController extends Controller
{
    private const PROTECTED_ROLES = [
        'Super Administrador',
        'Administrador',
        'Docente',
        'Estudiante',
    ];

    public function index(Request $request): Response
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with([
                'permissions:id,name',
                'users:id,name,email',
            ])
            ->withCount(['permissions', 'users'])
            ->orderByRaw(
                "CASE name
                    WHEN 'Super Administrador' THEN 1
                    WHEN 'Administrador' THEN 2
                    WHEN 'Docente' THEN 3
                    WHEN 'Estudiante' THEN 4
                    ELSE 5
                END"
            )
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions_count,
                'users_count' => $role->users_count,
                'protected' => in_array($role->name, self::PROTECTED_ROLES, true),
                'editable' => $role->name !== 'Super Administrador',
                'permissions' => $role->permissions->pluck('name')->values(),
                'users' => $role->users,
            ]);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(function (Permission $permission) {
                $prefix = Str::before($permission->name, '.');

                return $prefix === $permission->name ? 'general' : $prefix;
            })
            ->map(fn ($group) => $group->values())
            ->sortKeys();

        return Inertia::render('Sistema/RolesPermisos/Index', [
            'roles' => $roles,
            'permisosAgrupados' => $permissions,
            'puedeEditar' => $request->user()->can('roles.editar'),
        ]);
    }

    public function update(
        UpdateRolPermisosRequest $request,
        Role $rol,
    ): RedirectResponse {
        if ($rol->name === 'Super Administrador') {
            throw ValidationException::withMessages([
                'permissions' => 'Los permisos del rol Super Administrador están protegidos.',
            ]);
        }

        $rol->syncPermissions($request->validated('permissions'));

        return back()->with('success', "Permisos del rol {$rol->name} actualizados correctamente.");
    }
}
