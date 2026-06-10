<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarGrupoAcademicoAction;
use App\Domains\Academico\DTOs\GrupoAcademicoData;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Services\AcademicoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\GrupoAcademicoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GrupoAcademicoController extends Controller
{
    public function index(Request $request, AcademicoService $service): Response
    {
        $filters = $request->validate([
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'turno_grupo' => ['nullable', 'string', 'max:80'],
            'nivel_grupo' => ['nullable', 'string', 'max:100'],
            'estado_grupo' => ['nullable', 'in:activo,inactivo'],
        ]);

        return Inertia::render('Institucional/Grupos/Index', [
            ...$service->grupos($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(GrupoAcademicoRequest $request, GuardarGrupoAcademicoAction $action): RedirectResponse
    {
        $action->execute(GrupoAcademicoData::fromArray($request->validated()));

        return back()->with('success', 'Grupo académico registrado correctamente.');
    }

    public function update(
        GrupoAcademicoRequest $request,
        GrupoAcademico $grupo,
        GuardarGrupoAcademicoAction $action,
    ): RedirectResponse {
        $action->execute(GrupoAcademicoData::fromArray($request->validated()), $grupo);

        return back()->with('success', 'Grupo académico actualizado correctamente.');
    }
}
