<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarProgramaAcademicoAction;
use App\Domains\Academico\DTOs\ProgramaAcademicoData;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Services\AcademicoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\ProgramaAcademicoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgramaAcademicoController extends Controller
{
    public function index(Request $request, AcademicoService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'estado_prog' => ['nullable', 'in:activo,inactivo'],
            'universidad_objetivo_prog' => ['nullable', 'string', 'max:180'],
            'modalidad_prog' => ['nullable', 'string', 'max:100'],
        ]);

        return Inertia::render('Institucional/Programas/Index', [
            ...$service->programas($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(
        ProgramaAcademicoRequest $request,
        GuardarProgramaAcademicoAction $action,
    ): RedirectResponse {
        $action->execute(ProgramaAcademicoData::fromArray($request->validated()));

        return back()->with('success', 'Programa académico registrado correctamente.');
    }

    public function update(
        ProgramaAcademicoRequest $request,
        ProgramaAcademico $programa,
        GuardarProgramaAcademicoAction $action,
    ): RedirectResponse {
        $action->execute(ProgramaAcademicoData::fromArray($request->validated()), $programa);

        return back()->with('success', 'Programa académico actualizado correctamente.');
    }
}
