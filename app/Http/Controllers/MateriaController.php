<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Actions\GuardarMateriaAction;
use App\Domains\Evaluaciones\DTOs\MateriaData;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Services\MateriaService;
use App\Http\Requests\Evaluaciones\MateriaEstadoRequest;
use App\Http\Requests\Evaluaciones\MateriaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MateriaController extends Controller
{
    public function index(Request $request, MateriaService $service): Response
    {
        $canManage = $request->user()->cuentaActiva()
            && $request->user()->hasRole('Super Administrador');

        return Inertia::render('Catalogos/Index', [
            ...$service->index(),
            'permisos' => [
                'crear' => $canManage && $request->user()->can('materias.crear'),
                'editar' => $canManage && $request->user()->can('materias.editar'),
                'cambiarEstado' => $canManage && $request->user()->can('materias.cambiar_estado'),
            ],
        ]);
    }

    public function store(MateriaRequest $request, GuardarMateriaAction $action): RedirectResponse
    {
        $action->execute(MateriaData::fromArray($request->validated()));

        return back()->with('success', 'Materia registrada correctamente.');
    }

    public function update(MateriaRequest $request, Materia $materia, GuardarMateriaAction $action): RedirectResponse
    {
        $action->execute(MateriaData::fromArray($request->validated()), $materia);

        return back()->with('success', 'Materia actualizada correctamente.');
    }

    public function changeStatus(MateriaEstadoRequest $request, Materia $materia, MateriaService $service): RedirectResponse
    {
        $service->changeStatus($materia, $request->validated('estado_mat'));

        return back()->with('success', 'Estado de la materia actualizado correctamente.');
    }
}
