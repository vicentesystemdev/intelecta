<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Actions\ActualizarPreguntaAction;
use App\Domains\Evaluaciones\Actions\CambiarEstadoPreguntaAction;
use App\Domains\Evaluaciones\Actions\CrearPreguntaAction;
use App\Domains\Evaluaciones\Actions\ListarPreguntasAction;
use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Services\PreguntaService;
use App\Http\Requests\Evaluaciones\StorePreguntaRequest;
use App\Http\Requests\Evaluaciones\UpdatePreguntaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreguntaController extends Controller
{
    public function index(Request $request, ListarPreguntasAction $action): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_mat' => ['nullable', 'integer', 'exists:materias,id_mat'],
            'id_area' => ['nullable', 'integer', 'exists:areas_conocimiento,id_area'],
            'id_tem' => ['nullable', 'integer', 'exists:temas,id_tem'],
            'dificultad_preg' => ['nullable', 'in:basica,media,avanzada'],
            'tipo_preg' => ['nullable', 'in:opcion_multiple,verdadero_falso,respuesta_corta'],
            'estado_preg' => ['nullable', 'in:activo,inactivo'],
        ]);

        $data = $action->execute($filters);
        if (isset($data['preguntas'])) {
            $data['preguntas']->getCollection()->load(['alternativas']);
        }

        return Inertia::render('Evaluaciones/Preguntas/Index', [
            ...$data,
            'filtros' => $filters,
            'permisos' => $this->permissions($request),
        ]);
    }

    public function create(PreguntaService $service): Response
    {
        return Inertia::render('Evaluaciones/Preguntas/Create', ['opciones' => $service->options()]);
    }

    public function store(StorePreguntaRequest $request, CrearPreguntaAction $action): RedirectResponse
    {
        $pregunta = $action->execute(PreguntaData::fromArray($request->validated()));

        return to_route('preguntas.index')->with('success', 'Pregunta incorporada al banco académico.');
    }

    public function show(Request $request, Pregunta $pregunta, PreguntaService $service): Response
    {
        return Inertia::render('Evaluaciones/Preguntas/Show', [
            'pregunta' => $service->find($pregunta->getKey()),
            'permisos' => $this->permissions($request),
        ]);
    }

    public function edit(Pregunta $pregunta, PreguntaService $service): Response
    {
        return Inertia::render('Evaluaciones/Preguntas/Edit', [
            'pregunta' => $service->find($pregunta->getKey()),
            'opciones' => $service->options(),
        ]);
    }

    public function update(
        UpdatePreguntaRequest $request,
        Pregunta $pregunta,
        ActualizarPreguntaAction $action,
    ): RedirectResponse {
        $action->execute($pregunta, PreguntaData::fromArray($request->validated()));

        return to_route('preguntas.index')->with('success', 'Pregunta actualizada correctamente.');
    }

    public function cambiarEstado(Pregunta $pregunta, CambiarEstadoPreguntaAction $action): RedirectResponse
    {
        $actualizada = $action->execute($pregunta);

        return back()->with('success', "La pregunta ahora se encuentra {$actualizada->estado_preg}.");
    }

    private function permissions(Request $request): array
    {
        return [
            'crear' => $request->user()->can('preguntas.crear'),
            'editar' => $request->user()->can('preguntas.editar'),
            'cambiarEstado' => $request->user()->can('preguntas.eliminar'),
        ];
    }
}
