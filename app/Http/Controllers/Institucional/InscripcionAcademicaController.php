<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarInscripcionAcademicaAction;
use App\Domains\Academico\DTOs\InscripcionAcademicaData;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Services\AcademicoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\InscripcionAcademicaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InscripcionAcademicaController extends Controller
{
    public function index(Request $request, AcademicoService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_inscripcion' => ['nullable', 'in:activo,inactivo'],
        ]);

        return Inertia::render('Institucional/Inscripciones/Index', [
            ...$service->inscripciones($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(InscripcionAcademicaRequest $request, GuardarInscripcionAcademicaAction $action): RedirectResponse
    {
        $action->execute(InscripcionAcademicaData::fromArray($request->validated()));

        return back()->with('success', 'Inscripción académica registrada correctamente.');
    }

    public function update(
        InscripcionAcademicaRequest $request,
        InscripcionAcademica $inscripcion,
        GuardarInscripcionAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(InscripcionAcademicaData::fromArray($request->validated()), $inscripcion);

        return back()->with('success', 'Inscripción académica actualizada correctamente.');
    }
}
