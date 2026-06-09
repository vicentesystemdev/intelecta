<?php

namespace App\Http\Controllers;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReporteAcademicoController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Métricas superiores
        $totalPostulantes = Postulante::count();
        $totalColegios = Colegio::count();
        $totalUniversidades = Universidad::count();
        $totalCarreras = Carrera::count();
        $totalPreguntas = Pregunta::count();
        $totalPlantillas = PlantillaEvaluacion::count();

        // 2. Postulantes por universidad
        $postulantesPorUniversidad = Postulante::join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->join('universidades', 'carreras.id_uni', '=', 'universidades.id_uni')
            ->select('universidades.sigla_uni', 'universidades.nombre_uni', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('universidades.id_uni', 'universidades.sigla_uni', 'universidades.nombre_uni')
            ->orderBy('total', 'desc')
            ->get();

        // 3. Postulantes por carrera (ranking)
        $postulantesPorCarrera = Postulante::join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->select('carreras.nombre_car', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('carreras.id_car', 'carreras.nombre_car')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // 4. Postulantes por colegio
        $postulantesPorColegio = Postulante::join('colegios', 'postulantes.id_col', '=', 'colegios.id_col')
            ->select('colegios.nombre_col', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('colegios.id_col', 'colegios.nombre_col')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // 5. Cantidad de preguntas por área de conocimiento
        $preguntasPorArea = Pregunta::join('temas', 'preguntas.id_tem', '=', 'temas.id_tem')
            ->join('areas_conocimiento', 'temas.id_area', '=', 'areas_conocimiento.id_area')
            ->select('areas_conocimiento.nombre_area', DB::raw('count(preguntas.id_preg) as total'))
            ->groupBy('areas_conocimiento.id_area', 'areas_conocimiento.nombre_area')
            ->orderBy('total', 'desc')
            ->get();

        // 6. Cantidad de preguntas por dificultad
        $preguntasPorDificultad = Pregunta::select('dificultad_preg', DB::raw('count(id_preg) as total'))
            ->groupBy('dificultad_preg')
            ->orderBy('total', 'desc')
            ->get();

        // 7. Plantillas evaluativas detalladas
        $plantillasDetalle = PlantillaEvaluacion::withCount('preguntas')
            ->get()
            ->map(function ($plantilla) {
                return [
                    'id_plan' => $plantilla->id_plan,
                    'nombre_plan' => $plantilla->nombre_plan,
                    'dificultad_plan' => $plantilla->dificultad_plan,
                    'preguntas_count' => $plantilla->preguntas_count,
                    'puntaje_total' => (float) $plantilla->preguntas()->sum('plantilla_preguntas.puntaje_pp'),
                    'estado_plan' => $plantilla->estado_plan,
                ];
            });

        // 8. Distribución por nivel de exigencia matemática de carrera
        $exigenciaCarrera = Postulante::join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->select('carreras.nivel_exigencia_matematica_car as nivel', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('carreras.nivel_exigencia_matematica_car')
            ->orderBy('total', 'desc')
            ->get();

        return Inertia::render('Reportes/Index', [
            'metricas' => [
                'totalPostulantes' => $totalPostulantes,
                'totalColegios' => $totalColegios,
                'totalUniversidades' => $totalUniversidades,
                'totalCarreras' => $totalCarreras,
                'totalPreguntas' => $totalPreguntas,
                'totalPlantillas' => $totalPlantillas,
            ],
            'postulantesPorUniversidad' => $postulantesPorUniversidad,
            'postulantesPorCarrera' => $postulantesPorCarrera,
            'postulantesPorColegio' => $postulantesPorColegio,
            'preguntasPorArea' => $preguntasPorArea,
            'preguntasPorDificultad' => $preguntasPorDificultad,
            'plantillasDetalle' => $plantillasDetalle,
            'exigenciaCarrera' => $exigenciaCarrera,
        ]);
    }
}
