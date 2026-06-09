<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Actions\ActualizarTemaAction;
use App\Domains\Evaluaciones\Actions\CrearTemaAction;
use App\Domains\Evaluaciones\Actions\ListarTemasAction;
use App\Domains\Evaluaciones\DTOs\TemaData;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Evaluaciones\Services\TemaService;
use App\Http\Requests\Evaluaciones\StoreTemaRequest;
use App\Http\Requests\Evaluaciones\UpdateTemaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemaController extends Controller
{
    public function index(Request $request, ListarTemasAction $action): Response
    {
        $filters = $request->validate(['id_area' => ['nullable', 'integer', 'exists:areas_conocimiento,id_area']]);

        return Inertia::render('Evaluaciones/Temas/Index', [
            ...$action->execute($filters),
            'filtros' => $filters,
            'permisos' => $this->permissions($request),
        ]);
    }

    public function create(TemaService $service): Response
    {
        return Inertia::render('Evaluaciones/Temas/Create', ['areas' => $service->areas()]);
    }

    public function store(StoreTemaRequest $request, CrearTemaAction $action): RedirectResponse
    {
        $action->execute(TemaData::fromArray($request->validated()));

        return to_route('temas.index')->with('success', 'Tema académico registrado correctamente.');
    }

    public function edit(Tema $tema, TemaService $service): Response
    {
        return Inertia::render('Evaluaciones/Temas/Edit', [
            'tema' => $tema->load('area'),
            'areas' => $service->areas(),
        ]);
    }

    public function update(UpdateTemaRequest $request, Tema $tema, ActualizarTemaAction $action): RedirectResponse
    {
        $action->execute($tema, TemaData::fromArray($request->validated()));

        return to_route('temas.index')->with('success', 'Tema académico actualizado correctamente.');
    }

    private function permissions(Request $request): array
    {
        return [
            'crear' => $request->user()->can('temas.crear'),
            'editar' => $request->user()->can('temas.editar'),
        ];
    }
}
