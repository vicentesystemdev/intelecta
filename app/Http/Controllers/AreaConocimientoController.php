<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Actions\ActualizarAreaConocimientoAction;
use App\Domains\Evaluaciones\Actions\CrearAreaConocimientoAction;
use App\Domains\Evaluaciones\Actions\ListarAreasConocimientoAction;
use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Services\AreaConocimientoService;
use App\Http\Requests\Evaluaciones\StoreAreaConocimientoRequest;
use App\Http\Requests\Evaluaciones\UpdateAreaConocimientoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AreaConocimientoController extends Controller
{
    public function index(
        Request $request,
        ListarAreasConocimientoAction $action,
        AreaConocimientoService $service,
    ): Response {
        $filters = $request->validate([
            'id_mat' => ['nullable', 'integer', 'exists:materias,id_mat'],
        ], [
            'id_mat.integer' => 'La materia seleccionada no tiene un identificador válido.',
            'id_mat.exists' => 'La materia seleccionada no existe.',
        ]);
        return Inertia::render('Evaluaciones/Areas/Index', [
            'areas' => $action->execute($filters),
            'materias' => $service->materias(),
            'filtros' => $filters,
            'permisos' => $this->permissions($request),
        ]);
    }

    public function create(AreaConocimientoService $service): Response
    {
        return Inertia::render('Evaluaciones/Areas/Create', [
            'materias' => $service->materias(),
        ]);
    }

    public function store(StoreAreaConocimientoRequest $request, CrearAreaConocimientoAction $action): RedirectResponse
    {
        $action->execute(AreaConocimientoData::fromArray($request->validated()));

        return to_route('areas-conocimiento.index')->with('success', 'Área de conocimiento registrada correctamente.');
    }

    public function edit(AreaConocimiento $area, AreaConocimientoService $service): Response
    {
        return Inertia::render('Evaluaciones/Areas/Edit', [
            'area' => $area->load('materia'),
            'materias' => $service->materias(),
        ]);
    }

    public function update(
        UpdateAreaConocimientoRequest $request,
        AreaConocimiento $area,
        ActualizarAreaConocimientoAction $action,
    ): RedirectResponse {
        $action->execute($area, AreaConocimientoData::fromArray($request->validated()));

        return to_route('areas-conocimiento.index')->with('success', 'Área de conocimiento actualizada correctamente.');
    }

    private function permissions(Request $request): array
    {
        return [
            'crear' => $request->user()->can('areas.crear'),
            'editar' => $request->user()->can('areas.editar'),
        ];
    }
}
