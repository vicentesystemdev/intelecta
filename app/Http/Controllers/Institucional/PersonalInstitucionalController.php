<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Institucional\Actions\VincularUsuarioPersonalAction;
use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Services\OrganizacionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\EstadoOrganizacionalRequest;
use App\Http\Requests\Institucional\PersonalInstitucionalRequest;
use App\Http\Requests\Institucional\VincularPersonalRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonalInstitucionalController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['buscar' => ['nullable', 'string', 'max:160'], 'estado' => ['nullable', 'in:pendiente,activo,inactivo']]);
        $query = PersonalInstitucional::with(['cargo', 'user:id,name,email,estado_cuenta']);
        if ($search = $filters['buscar'] ?? null) {
            $query->where(function ($query) use ($search): void {
                foreach (['nombres', 'apellidos', 'ci', 'correo_contacto'] as $column) {
                    $query->orWhereRaw('LOWER('.$column.') LIKE LOWER(?)', ['%'.$search.'%']);
                }
            });
        }
        if ($state = $filters['estado'] ?? null) {
            $query->where('estado', $state);
        }

        return Inertia::render('Institucional/Personal/Index', [
            'personal' => $query->orderBy('apellidos')->orderBy('id_personal')->paginate(15)->withQueryString(),
            'cargosActivos' => Cargo::where('estado', EstadoCargo::ACTIVO)->orderBy('nombre_cargo')->get(['id_cargo', 'nombre_cargo']),
            'filtros' => $filters,
            'permisos' => collect(['crear', 'editar', 'cambiar_estado'])->mapWithKeys(fn ($action) => [$action => $request->user()->canManageOrganization('personal.'.$action)])
                ->put('vincular', $request->user()->canChangeLoginEmail()),
        ]);
    }

    public function usuariosElegibles(Request $request): JsonResponse
    {
        abort_unless($request->user()->canChangeLoginEmail(), 403);
        // Deliberate human search, not contact matching or an indiscriminate account dump.
        $filters = $request->validate(['buscar' => ['required', 'string', 'min:1', 'max:160']]);
        $query = User::whereDoesntHave('personalInstitucional');
        $query->where(function ($query) use ($filters): void {
            $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$filters['buscar'].'%'])
                ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ['%'.$filters['buscar'].'%']);
            if (filter_var($filters['buscar'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
                $query->orWhere('id', $filters['buscar']);
            }
        });

        return response()->json($query->orderBy('id')->paginate(20, ['id', 'name', 'email', 'estado_cuenta']));
    }

    public function store(PersonalInstitucionalRequest $request, OrganizacionService $service): RedirectResponse
    {
        $service->savePersonal($request->validated(), $request->user());

        return back()->with('success', 'Personal registrado como pendiente, sin crear ni vincular una cuenta.');
    }

    public function update(PersonalInstitucionalRequest $request, PersonalInstitucional $personal, OrganizacionService $service): RedirectResponse
    {
        $service->savePersonal($request->validated(), $request->user(), $personal);

        return back()->with('success', 'Datos institucionales actualizados.');
    }

    public function cambiarEstado(EstadoOrganizacionalRequest $request, PersonalInstitucional $personal, OrganizacionService $service): RedirectResponse
    {
        $service->changeState($personal, EstadoPersonal::from($request->validated('estado')), $request->user());

        return back()->with('success', 'Estado institucional actualizado. La cuenta digital no cambió.');
    }

    public function vincular(VincularPersonalRequest $request, PersonalInstitucional $personal, VincularUsuarioPersonalAction $action): RedirectResponse
    {
        $action->execute((int) $request->validated('user_id'), $personal->id_personal, $request->user(), $request->validated('motivo'));

        return back()->with('success', 'Vínculo de identidad confirmado por IDs explícitos.');
    }
}
