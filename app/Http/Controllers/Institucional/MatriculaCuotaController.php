<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarCuotaAcademicaAction;
use App\Domains\Academico\Actions\GuardarMatriculaAcademicaAction;
use App\Domains\Academico\DTOs\CuotaAcademicaData;
use App\Domains\Academico\DTOs\MatriculaAcademicaData;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Services\MatriculaCuotaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\CuotaAcademicaRequest;
use App\Http\Requests\Institucional\MatriculaAcademicaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MatriculaCuotaController extends Controller
{
    public function index(Request $request, MatriculaCuotaService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_matricula_mat' => ['nullable', 'in:activa,observada,inactiva,becada,exenta'],
            'estado_cuota' => ['nullable', 'in:pendiente,pagada,vencida,becada,exenta'],
        ]);

        return Inertia::render('Institucional/MatriculasCuotas/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function storeMatricula(
        MatriculaAcademicaRequest $request,
        GuardarMatriculaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(MatriculaAcademicaData::fromArray($request->validated()));

        return back()->with('success', 'Matrícula académica registrada correctamente.');
    }

    public function updateMatricula(
        MatriculaAcademicaRequest $request,
        MatriculaAcademica $matricula,
        GuardarMatriculaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(MatriculaAcademicaData::fromArray($request->validated()), $matricula);

        return back()->with('success', 'Matrícula académica actualizada correctamente.');
    }

    public function storeCuota(
        CuotaAcademicaRequest $request,
        GuardarCuotaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(CuotaAcademicaData::fromArray($request->validated()));

        return back()->with('success', 'Cuota académica registrada correctamente.');
    }

    public function updateCuota(
        CuotaAcademicaRequest $request,
        CuotaAcademica $cuota,
        GuardarCuotaAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(CuotaAcademicaData::fromArray($request->validated()), $cuota);

        return back()->with('success', 'Cuota académica actualizada correctamente.');
    }
}
