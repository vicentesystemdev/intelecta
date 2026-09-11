<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarAsistenciaAcademicaAction;
use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Services\AmbitoDocenteService;
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
    public function index(
        Request $request,
        AsistenciaAcademicaService $service,
        AmbitoDocenteService $ambito,
    ): Response {
        $filters = $request->validate([
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'fecha_asist' => ['nullable', 'date'],
            'sesion_asist' => ['nullable', 'string', 'max:120'],
            'estado_asist' => ['nullable', 'in:presente,ausente,retraso,justificado'],
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
        ]);

        if (isset($filters['id_prog'])) {
            abort_unless($ambito->puedeVerPrograma($request->user(), (int) $filters['id_prog'], 'asistencia.ver'), 403);
        }
        if (isset($filters['id_grupo'])) {
            abort_unless($ambito->puedeVerGrupo($request->user(), (int) $filters['id_grupo'], 'asistencia.ver'), 403);
        }
        if (isset($filters['id_tutor']) && $ambito->esDocenteRestringido($request->user(), 'asistencia.ver')) {
            abort_unless($ambito->tutorId($request->user(), 'asistencia.ver') === (int) $filters['id_tutor'], 403);
        }

        return Inertia::render('Institucional/Asistencia/Index', [
            ...$service->index($filters, $request->user()),
            'filtros' => $filters,
            'permisos' => [
                'crear' => $request->user()->can('asistencia.crear'),
                'editar' => $request->user()->can('asistencia.editar'),
            ],
        ]);
    }

    public function store(
        AsistenciaAcademicaRequest $request,
        GuardarAsistenciaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(
            AsistenciaAcademicaData::fromArray($request->validated()),
            $request->user(),
        );

        return back()->with('success', 'Asistencia académica registrada correctamente.');
    }

    public function update(
        AsistenciaAcademicaRequest $request,
        AsistenciaAcademica $asistencia,
        GuardarAsistenciaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(
            AsistenciaAcademicaData::fromArray($request->validated()),
            $request->user(),
            $asistencia,
        );

        return back()->with('success', 'Asistencia académica actualizada correctamente.');
    }

    public function storeGroup(
        AsistenciaGrupoRequest $request,
        GuardarAsistenciaAcademicaAction $action,
    ): RedirectResponse {
        $action->executeGroup($request->validated(), $request->user());

        return back()->with('success', 'Asistencia del grupo consolidada correctamente.');
    }
}
