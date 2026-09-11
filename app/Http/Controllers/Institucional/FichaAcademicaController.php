<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Services\AcademicoService;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Domains\Postulantes\Models\Postulante;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FichaAcademicaController extends Controller
{
    public function index(
        Request $request,
        AcademicoService $service,
        AmbitoDocenteService $ambito,
    ): Response {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'nivel_riesgo_rend' => [
                'nullable',
                'in:Alto rendimiento,Seguimiento regular,Atención prioritaria',
            ],
        ], [
            'buscar.max' => 'La búsqueda no puede superar los 160 caracteres.',
            'id_prog.integer' => 'El programa académico seleccionado no tiene un identificador válido.',
            'id_prog.exists' => 'El programa académico seleccionado no existe.',
            'id_grupo.integer' => 'El grupo académico seleccionado no tiene un identificador válido.',
            'id_grupo.exists' => 'El grupo académico seleccionado no existe.',
            'nivel_riesgo_rend.in' => 'Seleccione un estado de seguimiento válido.',
        ]);

        if (isset($filters['id_prog'])) {
            abort_unless($ambito->puedeVerPrograma($request->user(), (int) $filters['id_prog'], 'ficha-academica.ver'), 403);
        }
        if (isset($filters['id_grupo'])) {
            abort_unless($ambito->puedeVerGrupo($request->user(), (int) $filters['id_grupo'], 'ficha-academica.ver'), 403);
        }

        return Inertia::render('Institucional/FichaAcademica/Index', [
            ...$service->fichas($filters, $request->user()),
            'filtros' => $filters,
            'vistaDocente' => $ambito->esDocenteRestringido($request->user(), 'ficha-academica.ver'),
        ]);
    }

    public function show(
        Request $request,
        Postulante $postulante,
        AcademicoService $service,
        AmbitoDocenteService $ambito,
    ): Response {
        Gate::authorize('viewAcademicFile', $postulante);

        return Inertia::render('Institucional/FichaAcademica/Show', [
            ...$service->ficha($postulante, $request->user()),
            'vistaDocente' => $ambito->esDocenteRestringido($request->user(), 'ficha-academica.ver'),
        ]);
    }
}
