<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Http\Requests\Admin\UpdateUsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $users = User::query()
            ->with('roles:id,name')
            ->when($filters['buscar'] ?? null, function ($query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';

                $query->where(function ($query) use ($pattern) {
                    $query
                        ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$pattern]);
                });
            })
            ->when(
                $filters['role'] ?? null,
                fn ($query, string $role) => $query->role($role)
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Sistema/Usuarios/Index', [
            'usuarios' => $users,
            'roles' => $roles,
            'filtros' => $filters,
            'metricas' => [
                'total' => User::query()->count(),
                'administradores' => User::role(['Super Administrador', 'Administrador'])->count(),
                'docentes' => User::role('Docente')->count(),
                'estudiantes' => User::role('Estudiante')->count(),
            ],
            'permisos' => [
                'crear' => $request->user()->can('usuarios.crear'),
                'editar' => $request->user()->can('usuarios.editar'),
                'asignarSuperAdministrador' => $request->user()->hasRole('Super Administrador'),
            ],
            'usuarioActualId' => $request->user()->getKey(),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->ensureRoleCanBeAssigned($request, $data['role']);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles([$data['role']]);
        });

        return back()->with('success', 'Usuario institucional registrado correctamente.');
    }

    public function update(
        UpdateUsuarioRequest $request,
        User $usuario,
    ): RedirectResponse {
        $data = $request->validated();
        $this->ensureSuperAdministratorCanBeEdited($request, $usuario);
        $this->ensureRoleCanBeAssigned($request, $data['role']);
        $this->protectLastSuperAdministrator($usuario, $data['role']);

        DB::transaction(function () use ($usuario, $data): void {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $usuario->update($attributes);
            $usuario->syncRoles([$data['role']]);
        });

        return back()->with('success', 'Información del usuario actualizada correctamente.');
    }

    private function ensureRoleCanBeAssigned(Request $request, string $role): void
    {
        if (
            $role === 'Super Administrador'
            && ! $request->user()->hasRole('Super Administrador')
        ) {
            throw ValidationException::withMessages([
                'role' => 'Solo un Super Administrador puede asignar este rol.',
            ]);
        }
    }

    private function ensureSuperAdministratorCanBeEdited(
        Request $request,
        User $usuario,
    ): void {
        if (
            $usuario->hasRole('Super Administrador')
            && ! $request->user()->hasRole('Super Administrador')
        ) {
            throw ValidationException::withMessages([
                'role' => 'Solo un Super Administrador puede modificar esta cuenta.',
            ]);
        }
    }

    private function protectLastSuperAdministrator(User $usuario, string $newRole): void
    {
        if (
            $usuario->hasRole('Super Administrador')
            && $newRole !== 'Super Administrador'
            && User::role('Super Administrador')->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'No es posible degradar al último Super Administrador.',
            ]);
        }
    }
}
