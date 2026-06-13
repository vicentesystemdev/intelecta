<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarTutorAcademicoAction;
use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Services\TutorAcademicoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\TutorAcademicoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TutorAcademicoController extends Controller
{
    public function index(Request $request, TutorAcademicoService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'especialidad_tutor' => ['nullable', 'string', 'max:160'],
            'estado_tutor' => ['nullable', 'in:activo,inactivo'],
        ]);

        return Inertia::render('Institucional/Tutores/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(
        TutorAcademicoRequest $request,
        GuardarTutorAcademicoAction $action,
    ): RedirectResponse {
        $action->execute(TutorAcademicoData::fromArray($request->validated()));

        return back()->with('success', 'Tutor académico registrado correctamente.');
    }

    public function update(
        TutorAcademicoRequest $request,
        TutorAcademico $tutor,
        GuardarTutorAcademicoAction $action,
    ): RedirectResponse {
        $action->execute(TutorAcademicoData::fromArray($request->validated()), $tutor);

        return back()->with('success', 'Perfil del tutor actualizado correctamente.');
    }

    public function show(TutorAcademico $tutor, TutorAcademicoService $service): Response
    {
        return Inertia::render('Institucional/Tutores/Show', [
            'tutor' => $service->show($tutor),
        ]);
    }
}
