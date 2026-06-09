<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Actions\ActualizarAreaConocimientoAction;
use App\Domains\Evaluaciones\Actions\CrearAreaConocimientoAction;
use App\Domains\Evaluaciones\Actions\ListarAreasConocimientoAction;
use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Http\Requests\Evaluaciones\StoreAreaConocimientoRequest;
use App\Http\Requests\Evaluaciones\UpdateAreaConocimientoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AreaConocimientoController extends Controller
{
    public function index(Request $request, ListarAreasConocimientoAction $action): Response
    {
        return Inertia::render('Evaluaciones/Areas/Index', [
            'areas' => $action->execute(),
            'permisos' => $this->permissions($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Evaluaciones/Areas/Create');
    }

    public function store(StoreAreaConocimientoRequest $request, CrearAreaConocimientoAction $action): RedirectResponse
    {
        $action->execute(AreaConocimientoData::fromArray($request->validated()));

        return to_route('areas-conocimiento.index')->with('success', 'Área de conocimiento registrada correctamente.');
    }

    public function edit(AreaConocimiento $area): Response
    {
        return Inertia::render('Evaluaciones/Areas/Edit', ['area' => $area]);
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
