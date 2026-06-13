<?php

namespace App\Http\Controllers;

use App\Domains\Academico\Services\AcademicoService;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Reportes\Services\CoberturaCurricularService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        CoberturaCurricularService $cobertura,
        AcademicoService $academico,
    ): Response|RedirectResponse
    {
        if (auth()->user()->hasRole('Estudiante')) {
            return redirect('/');
        }

        $plantillasRecientes = PlantillaEvaluacion::query()
            ->withCount('preguntas')
            ->with('preguntas.tema.area.materia:id_mat,codigo_mat,nombre_mat')
            ->latest('id_plan')
            ->take(5)
            ->get()
            ->map(fn (PlantillaEvaluacion $plantilla) => [
                'id_plan' => $plantilla->id_plan,
                'nombre_plan' => $plantilla->nombre_plan,
                'duracion_minutos_plan' => $plantilla->duracion_minutos_plan,
                'dificultad_plan' => $plantilla->dificultad_plan,
                'estado_plan' => $plantilla->estado_plan,
                'preguntas_count' => $plantilla->preguntas_count,
                'materias' => $plantilla->preguntas
                    ->pluck('tema.area.materia.nombre_mat')
                    ->filter()
                    ->unique()
                    ->values(),
            ]);

        $postulantesPorCarrera = Postulante::query()
            ->join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->select('carreras.nombre_car', DB::raw('COUNT(postulantes.id_post) as total'))
            ->groupBy('carreras.id_car', 'carreras.nombre_car')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $gestionInstitucional = [
            'programasActivos' => 0,
            'gruposActivos' => 0,
            'postulantesInscritos' => 0,
            'proximosSimulacros' => 0,
            'promedioInstitucional' => 0,
            'seguimientoPrioritario' => 0,
            'tutoresActivos' => 0,
            'gruposConTutor' => 0,
            'asignacionesActivas' => 0,
            'matriculasActivas' => 0,
            'cuotasPendientes' => 0,
            'cuotasVencidas' => 0,
            'postulantesHabilitados' => 0,
            'postulantesRestringidos' => 0,
            'asistenciaPromedio' => 0,
            'presentesPeriodo' => 0,
            'ausenciasPeriodo' => 0,
            'gruposConAsistencia' => 0,
        ];

        if (
            Schema::hasTable('programas_academicos')
            && Schema::hasTable('grupos_academicos')
            && Schema::hasTable('inscripciones_academicas')
            && Schema::hasTable('simulacros_programados')
            && Schema::hasTable('rendimientos_postulante')
        ) {
            $gestionInstitucional = $academico->dashboardMetrics();
        }

        return Inertia::render('Admin/Dashboard', [
            'metricas' => [
                'postulantes' => Postulante::count(),
                'materias' => Materia::count(),
                'preguntas' => Pregunta::count(),
                'plantillasActivas' => PlantillaEvaluacion::where('estado_plan', 'activa')->count(),
                'areas' => AreaConocimiento::count(),
                'temas' => Tema::count(),
            ],
            'coberturaMaterias' => $cobertura->resumenMaterias(),
            'preguntasSinMateria' => $cobertura->preguntasSinMateria(),
            'plantillasRecientes' => $plantillasRecientes,
            'postulantesPorCarrera' => $postulantesPorCarrera,
            'gestionInstitucional' => $gestionInstitucional,
        ]);
    }
}
