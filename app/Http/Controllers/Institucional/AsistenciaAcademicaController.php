<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarAsistenciaAcademicaAction;
use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Services\AsistenciaAcademicaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\AsistenciaAcademicaRequest;
use App\Http\Requests\Institucional\AsistenciaGrupoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AsistenciaAcademicaController extends Controller
{
    public function index(Request $request, AsistenciaAcademicaService $service): Response
    {
        $filters = $request->validate([
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'fecha_asist' => ['nullable', 'date'],
            'sesion_asist' => ['nullable', 'string', 'max:120'],
            'estado_asist' => ['nullable', 'in:presente,ausente,retraso,justificado'],
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
        ]);

        return Inertia::render('Institucional/Asistencia/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(
        AsistenciaAcademicaRequest $request,
        GuardarAsistenciaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(AsistenciaAcademicaData::fromArray($request->validated()));

        return back()->with('success', 'Asistencia académica registrada correctamente.');
    }

    public function update(
        AsistenciaAcademicaRequest $request,
        AsistenciaAcademica $asistencia,
        GuardarAsistenciaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(AsistenciaAcademicaData::fromArray($request->validated()), $asistencia);

        return back()->with('success', 'Asistencia académica actualizada correctamente.');
    }

    public function storeGroup(
        AsistenciaGrupoRequest $request,
        GuardarAsistenciaAcademicaAction $action,
    ): RedirectResponse {
        $action->executeGroup($request->validated());

        return back()->with('success', 'Asistencia del grupo consolidada correctamente.');
    }
}
