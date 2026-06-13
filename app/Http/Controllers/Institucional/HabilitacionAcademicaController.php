<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\ActualizarHabilitacionAcademicaAction;
use App\Domains\Academico\DTOs\HabilitacionAcademicaData;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Services\HabilitacionAcademicaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\HabilitacionAcademicaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HabilitacionAcademicaController extends Controller
{
    public function index(Request $request, HabilitacionAcademicaService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_hab' => ['nullable', 'in:habilitado,observado,restringido,temporal'],
        ]);

        return Inertia::render('Institucional/HabilitacionAcademica/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function update(
        HabilitacionAcademicaRequest $request,
        HabilitacionAcademica $habilitacion,
        ActualizarHabilitacionAcademicaAction $action,
    ): RedirectResponse {
        $action->execute(
            $habilitacion,
            HabilitacionAcademicaData::fromArray($request->validated()),
        );

        return back()->with('success', 'Habilitación académica actualizada correctamente.');
    }
}
