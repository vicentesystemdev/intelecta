<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Services\AcademicoService;
use App\Domains\Postulantes\Models\Postulante;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class FichaAcademicaController extends Controller
{
    public function show(Postulante $postulante, AcademicoService $service): Response
    {
        return Inertia::render('Institucional/FichaAcademica/Show', $service->ficha($postulante));
    }
}
