<?php

namespace App\Http\Controllers\Estudiante;

use App\Domains\Academico\Services\AcademicoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeguimientoAcademicoController extends Controller
{
    public function ficha(Request $request, AcademicoService $service): Response
    {
        $postulante = $request->user()->postulante;

        return Inertia::render('Estudiante/MiFicha', [
            'postulanteVinculado' => true,
            ...$service->ficha($postulante),
        ]);
    }

    public function ranking(Request $request, AcademicoService $service): Response
    {
        $postulante = $request->user()->postulante;

        return Inertia::render('Estudiante/Ranking', [
            'postulanteVinculado' => true,
            ...$service->rankingPortal($postulante),
        ]);
    }
}
