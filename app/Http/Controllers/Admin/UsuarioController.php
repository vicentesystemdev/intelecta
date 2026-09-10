<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Seguridad\Services\CuentaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AsignarRolesUsuarioRequest;
use App\Http\Requests\Admin\OperacionCuentaRequest;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Http\Requests\Admin\UpdateUsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
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
                'crear' => $request->user()->canChangeLoginEmail(),
                'editar' => $request->user()->canChangeLoginEmail(),
                'seguridad' => $request->user()->canChangeLoginEmail(),
                'asignarRoles' => $request->user()->canChangeLoginEmail(),
                'cambiarCorreoAcceso' => $request->user()->canChangeLoginEmail(),
            ],
            'usuarioActualId' => $request->user()->getKey(),
        ]);
    }

    public function store(StoreUsuarioRequest $request, CuentaService $accounts): RedirectResponse
    {
        $accounts->create($request->user(), $request->validated());

        return back()->with('success', 'Cuenta pendiente creada. Se envió un enlace para que el titular establezca su contraseña.');
    }

    public function update(UpdateUsuarioRequest $request, User $usuario, CuentaService $accounts): RedirectResponse
    {
        $accounts->update($request->user(), $usuario->id, $request->validated());

        return back()->with('success', 'Cuenta actualizada. Los cambios de correo requieren una nueva verificación.');
    }

    public function bloquear(OperacionCuentaRequest $request, User $usuario, CuentaService $accounts): RedirectResponse
    {
        $accounts->block($request->user(), $usuario->id, $request->validated('motivo'));

        return back()->with('success', 'Cuenta bloqueada y acceso revocado.');
    }

    public function asignarRoles(AsignarRolesUsuarioRequest $request, User $usuario, CuentaService $accounts): RedirectResponse
    {
        $accounts->assignRoles($request->user(), $usuario->id, $request->validated());

        return back()->with('success', 'Roles actualizados. Si hubo cambios, se revocaron sesiones y enlaces anteriores; para una cuenta pendiente, reenvía la activación.');
    }

    public function desbloquear(OperacionCuentaRequest $request, User $usuario, CuentaService $accounts): RedirectResponse
    {
        $accounts->unblock($request->user(), $usuario->id);

        return back()->with('success', 'Cuenta desbloqueada según su verificación de correo.');
    }

    public function reenviarActivacion(OperacionCuentaRequest $request, User $usuario, CuentaService $accounts): RedirectResponse
    {
        $accounts->sendAccess($request->user(), $usuario->id);

        return back()->with('success', 'Se envió un nuevo enlace de establecimiento de contraseña.');
    }
}
