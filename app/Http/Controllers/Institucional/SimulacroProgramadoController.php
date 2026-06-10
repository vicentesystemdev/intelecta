<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarSimulacroProgramadoAction;
use App\Domains\Academico\DTOs\SimulacroProgramadoData;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Services\AcademicoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\SimulacroProgramadoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SimulacroProgramadoController extends Controller
{
    public function index(Request $request, AcademicoService $service): Response
    {
        $filters = $request->validate([
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_sim' => ['nullable', 'in:programado,en preparación,aplicado,cerrado'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        return Inertia::render('Institucional/Simulacros/Index', [
            ...$service->simulacros($filters),
            'filtros' => $filters,
        ]);
    }

    public function store(SimulacroProgramadoRequest $request, GuardarSimulacroProgramadoAction $action): RedirectResponse
    {
        $action->execute(SimulacroProgramadoData::fromArray($request->validated()));

        return back()->with('success', 'Simulacro programado correctamente.');
    }

    public function update(
        SimulacroProgramadoRequest $request,
        SimulacroProgramado $simulacro,
        GuardarSimulacroProgramadoAction $action,
    ): RedirectResponse {
        $action->execute(SimulacroProgramadoData::fromArray($request->validated()), $simulacro);

        return back()->with('success', 'Simulacro actualizado correctamente.');
    }
}
