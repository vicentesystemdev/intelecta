<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Services\OrganizacionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\CargoRequest;
use App\Http\Requests\Institucional\EstadoOrganizacionalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CargoController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['buscar' => ['nullable', 'string', 'max:160'], 'estado' => ['nullable', 'in:activo,inactivo']]);
        $query = Cargo::withCount('personal');
        if ($search = $filters['buscar'] ?? null) {
            $query->whereRaw('LOWER(nombre_cargo) LIKE LOWER(?)', ['%'.$search.'%']);
        }
        if ($state = $filters['estado'] ?? null) {
            $query->where('estado', $state);
        }

        return Inertia::render('Institucional/Cargos/Index', [
            'cargos' => $query->orderBy('nombre_cargo')->paginate(15)->withQueryString(),
            'filtros' => $filters,
            'permisos' => collect(['crear', 'editar', 'cambiar_estado'])->mapWithKeys(fn ($action) => [$action => $request->user()->canManageOrganization('cargos.'.$action)]),
        ]);
    }

    public function store(CargoRequest $request, OrganizacionService $service): RedirectResponse
    {
        $service->saveCargo($request->validated(), $request->user());

        return back()->with('success', 'Cargo registrado. No concede roles ni permisos.');
    }

    public function update(CargoRequest $request, Cargo $cargo, OrganizacionService $service): RedirectResponse
    {
        $service->saveCargo($request->validated(), $request->user(), $cargo);

        return back()->with('success', 'Cargo actualizado.');
    }

    public function cambiarEstado(EstadoOrganizacionalRequest $request, Cargo $cargo, OrganizacionService $service): RedirectResponse
    {
        $service->changeState($cargo, EstadoCargo::from($request->validated('estado')), $request->user());

        return back()->with('success', 'Estado del cargo actualizado. Se conservan sus asignaciones existentes.');
    }
}
