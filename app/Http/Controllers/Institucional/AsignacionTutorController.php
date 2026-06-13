<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarAsignacionTutorAction;
use App\Domains\Academico\DTOs\AsignacionTutorData;
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Services\AsignacionTutorService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\AsignacionTutorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AsignacionTutorController extends Controller
{
    public function index(Request $request, AsignacionTutorService $service): Response
    {
        $filters = $request->validate([
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_asig' => ['nullable', 'in:activo,inactivo'],
        ]);

        return Inertia::render('Institucional/AsignacionTutores/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(
        AsignacionTutorRequest $request,
        GuardarAsignacionTutorAction $action,
    ): RedirectResponse {
        $action->execute(AsignacionTutorData::fromArray($request->validated()));

        return back()->with('success', 'Asignación tutorial registrada correctamente.');
    }

    public function update(
        AsignacionTutorRequest $request,
        AsignacionTutor $asignacion,
        GuardarAsignacionTutorAction $action,
    ): RedirectResponse {
        $action->execute(AsignacionTutorData::fromArray($request->validated()), $asignacion);

        return back()->with('success', 'Asignación tutorial actualizada correctamente.');
    }
}
