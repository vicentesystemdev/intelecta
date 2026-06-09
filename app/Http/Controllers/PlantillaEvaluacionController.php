<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Actions\ActualizarPlantillaEvaluacionAction;
use App\Domains\Evaluaciones\Actions\CambiarEstadoPlantillaEvaluacionAction;
use App\Domains\Evaluaciones\Actions\CrearPlantillaEvaluacionAction;
use App\Domains\Evaluaciones\Actions\ListarPlantillasEvaluacionAction;
use App\Domains\Evaluaciones\DTOs\PlantillaEvaluacionData;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Services\PlantillaEvaluacionService;
use App\Http\Requests\Evaluaciones\StorePlantillaEvaluacionRequest;
use App\Http\Requests\Evaluaciones\UpdatePlantillaEvaluacionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlantillaEvaluacionController extends Controller
{
    public function index(
        Request $request,
        ListarPlantillasEvaluacionAction $action,
        PlantillaEvaluacionService $service
    ): Response {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_mat' => ['nullable', 'integer', 'exists:materias,id_mat'],
            'estado_plan' => ['nullable', 'in:activa,inactiva'],
        ]);

        $data = $action->execute($filters);
        if (isset($data['plantillas'])) {
            $data['plantillas']->getCollection()->load(['preguntas.tema.area.materia', 'preguntas.alternativas']);
        }

        return Inertia::render('Evaluaciones/Plantillas/Index', [
            ...$data,
            'preguntasDisponibles' => $service->questions(),
            'materias' => $service->subjects(),
            'filtros' => $filters,
            'permisos' => $this->permissions($request),
        ]);
    }

    public function create(PlantillaEvaluacionService $service): Response
    {
        return Inertia::render('Evaluaciones/Plantillas/Create', ['preguntasDisponibles' => $service->questions()]);
    }

    public function store(StorePlantillaEvaluacionRequest $request, CrearPlantillaEvaluacionAction $action): RedirectResponse
    {
        $plantilla = $action->execute(PlantillaEvaluacionData::fromArray($request->validated()));

        return to_route('plantillas-evaluacion.index')
            ->with('success', 'Plantilla de evaluación creada correctamente.');
    }

    public function show(
        Request $request,
        PlantillaEvaluacion $plantilla,
        PlantillaEvaluacionService $service,
    ): Response {
        return Inertia::render('Evaluaciones/Plantillas/Show', [
            'plantilla' => $service->find($plantilla->getKey()),
            'permisos' => $this->permissions($request),
        ]);
    }

    public function edit(PlantillaEvaluacion $plantilla, PlantillaEvaluacionService $service): Response
    {
        return Inertia::render('Evaluaciones/Plantillas/Edit', [
            'plantilla' => $service->find($plantilla->getKey()),
            'preguntasDisponibles' => $service->questions(),
        ]);
    }

    public function update(
        UpdatePlantillaEvaluacionRequest $request,
        PlantillaEvaluacion $plantilla,
        ActualizarPlantillaEvaluacionAction $action,
    ): RedirectResponse {
        $action->execute($plantilla, PlantillaEvaluacionData::fromArray($request->validated()));

        return to_route('plantillas-evaluacion.index')
            ->with('success', 'Plantilla de evaluación actualizada correctamente.');
    }

    public function cambiarEstado(
        PlantillaEvaluacion $plantilla,
        CambiarEstadoPlantillaEvaluacionAction $action,
    ): RedirectResponse {
        $actualizada = $action->execute($plantilla);

        return back()->with('success', "La plantilla ahora se encuentra {$actualizada->estado_plan}.");
    }

    private function permissions(Request $request): array
    {
        return [
            'crear' => $request->user()->can('plantillas.crear'),
            'editar' => $request->user()->can('plantillas.editar'),
            'cambiarEstado' => $request->user()->can('plantillas.eliminar'),
        ];
    }
}
