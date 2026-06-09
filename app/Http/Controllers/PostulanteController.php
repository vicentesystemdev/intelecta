<?php

namespace App\Http\Controllers;

use App\Domains\Postulantes\Actions\ActualizarPostulanteAction;
use App\Domains\Postulantes\Actions\CambiarEstadoPostulanteAction;
use App\Domains\Postulantes\Actions\CrearPostulanteAction;
use App\Domains\Postulantes\Actions\ListarPostulantesAction;
use App\Domains\Postulantes\DTOs\PostulanteData;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\PostulanteService;
use App\Http\Requests\Postulantes\StorePostulanteRequest;
use App\Http\Requests\Postulantes\UpdatePostulanteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostulanteController extends Controller
{
    public function index(Request $request, ListarPostulantesAction $action): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'id_col' => ['nullable', 'integer', 'exists:colegios,id_col'],
            'id_uni' => ['nullable', 'integer', 'exists:universidades,id_uni'],
            'id_car' => ['nullable', 'integer', 'exists:carreras,id_car'],
            'estado_post' => ['nullable', 'in:activo,inactivo'],
        ]);

        $result = $action->execute($filters);

        return Inertia::render('Postulantes/Index', [
            ...$result,
            'filtros' => $filters,
            'permisos' => $this->permissions($request),
        ]);
    }

    public function create(Request $request, PostulanteService $service): Response
    {
        return Inertia::render('Postulantes/Create', [
            'gestionActual' => now()->year,
            'opciones' => $service->formOptions(),
            'permisos' => $this->permissions($request),
        ]);
    }

    public function store(
        StorePostulanteRequest $request,
        CrearPostulanteAction $action,
    ): RedirectResponse {
        $postulante = $action->execute(
            PostulanteData::fromArray($request->validated())
        );

        return to_route('postulantes.index')
            ->with('success', 'Postulante registrado correctamente.');
    }

    public function show(
        Request $request,
        Postulante $postulante,
        PostulanteService $service,
    ): Response {
        return Inertia::render('Postulantes/Show', [
            'postulante' => $service->find($postulante->getKey()),
            'permisos' => $this->permissions($request),
        ]);
    }

    public function edit(
        Request $request,
        Postulante $postulante,
        PostulanteService $service,
    ): Response
    {
        return Inertia::render('Postulantes/Edit', [
            'postulante' => $service->find($postulante->getKey()),
            'opciones' => $service->formOptions(),
            'permisos' => $this->permissions($request),
        ]);
    }

    public function update(
        UpdatePostulanteRequest $request,
        Postulante $postulante,
        ActualizarPostulanteAction $action,
    ): RedirectResponse {
        $action->execute(
            $postulante,
            PostulanteData::fromArray($request->validated())
        );

        return to_route('postulantes.index')
            ->with('success', 'Información del postulante actualizada correctamente.');
    }

    public function cambiarEstado(
        Postulante $postulante,
        CambiarEstadoPostulanteAction $action,
    ): RedirectResponse {
        $actualizado = $action->execute($postulante);

        return back()->with(
            'success',
            "El postulante ahora se encuentra {$actualizado->estado_post}."
        );
    }

    /**
     * @return array<string, bool>
     */
    private function permissions(Request $request): array
    {
        return [
            'crear' => $request->user()->can('postulantes.crear'),
            'editar' => $request->user()->can('postulantes.editar'),
            'cambiarEstado' => $request->user()->can('postulantes.eliminar'),
        ];
    }
}
