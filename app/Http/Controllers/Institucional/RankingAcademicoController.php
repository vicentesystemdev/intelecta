<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Services\AcademicoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RankingAcademicoController extends Controller
{
    public function index(Request $request, AcademicoService $service): Response
    {
        $filters = $request->validate([
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'nivel_riesgo_rend' => ['nullable', 'in:Alto rendimiento,Seguimiento regular,Atención prioritaria'],
        ]);

        return Inertia::render('Institucional/Ranking/Index', [
            ...$service->ranking($filters),
            'filtros' => $filters,
        ]);
    }
}
