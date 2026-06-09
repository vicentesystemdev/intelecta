<?php

namespace App\Http\Controllers;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReporteAcademicoController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. KPIs principales
        $totalPostulantes = Postulante::count();
        $totalColegios = Colegio::count();
        $totalUniversidades = Universidad::count();
        $totalCarreras = Carrera::count();
        $totalPreguntas = Pregunta::count();
        $totalPlantillas = PlantillaEvaluacion::count();
        $totalAreas = AreaConocimiento::count();
        $totalTemas = Tema::count();

        // 2. Distribución: Postulantes por universidad
        $postulantesPorUniversidad = Postulante::join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->join('universidades', 'carreras.id_uni', '=', 'universidades.id_uni')
            ->select('universidades.sigla_uni', 'universidades.nombre_uni', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('universidades.id_uni', 'universidades.sigla_uni', 'universidades.nombre_uni')
            ->orderBy('total', 'desc')
            ->get();

        // 3. Distribución: Postulantes por carrera (ranking top 10)
        $postulantesPorCarrera = Postulante::join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->select('carreras.nombre_car', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('carreras.id_car', 'carreras.nombre_car')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // 4. Distribución: Postulantes por colegio (ranking top 10)
        $postulantesPorColegio = Postulante::join('colegios', 'postulantes.id_col', '=', 'colegios.id_col')
            ->select('colegios.nombre_col', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('colegios.id_col', 'colegios.nombre_col')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // 5. Distribución: Preguntas por área de conocimiento
        $preguntasPorArea = Pregunta::join('temas', 'preguntas.id_tem', '=', 'temas.id_tem')
            ->join('areas_conocimiento', 'temas.id_area', '=', 'areas_conocimiento.id_area')
            ->select('areas_conocimiento.nombre_area', DB::raw('count(preguntas.id_preg) as total'))
            ->groupBy('areas_conocimiento.id_area', 'areas_conocimiento.nombre_area')
            ->orderBy('total', 'desc')
            ->get();

        // 6. Distribución: Preguntas por dificultad
        $preguntasPorDificultad = Pregunta::select('dificultad_preg', DB::raw('count(id_preg) as total'))
            ->groupBy('dificultad_preg')
            ->orderBy('total', 'desc')
            ->get();

        // 7. Distribución: Postulantes por exigencia matemática de carrera
        $postulantesPorExigenciaMatematica = Postulante::join('carreras', 'postulantes.id_car', '=', 'carreras.id_car')
            ->select('carreras.nivel_exigencia_matematica_car as nivel', DB::raw('count(postulantes.id_post) as total'))
            ->groupBy('carreras.nivel_exigencia_matematica_car')
            ->orderBy('total', 'desc')
            ->get();

        // 8. Plantillas evaluativas detalladas
        $plantillasDetalle = PlantillaEvaluacion::withCount('preguntas')
            ->get()
            ->map(function ($plantilla) {
                return [
                    'id_plan' => $plantilla->id_plan,
                    'nombre_plan' => $plantilla->nombre_plan,
                    'dificultad_plan' => $plantilla->dificultad_plan,
                    'cantidad_preguntas' => $plantilla->preguntas_count,
                    'puntaje_total' => (float) $plantilla->preguntas()->sum('plantilla_preguntas.puntaje_pp'),
                    'estado_plan' => $plantilla->estado_plan,
                ];
            });

        // 9. Reporte individual por postulante
        $postulantesList = Postulante::with(['colegio', 'carrera.universidad'])
            ->get()
            ->map(function ($postulante) {
                $carrera = $postulante->carrera;
                $universidad = $carrera ? $carrera->universidad : null;
                $exigencia = $carrera ? $carrera->nivel_exigencia_matematica_car : 'baja-media';

                // Determinar perfil académico preliminar
                if ($exigencia === 'alta' || $exigencia === 'media-alta') {
                    $perfil = 'Requiere seguimiento intensivo';
                } elseif ($exigencia === 'media') {
                    $perfil = 'Seguimiento regular recomendado';
                } else {
                    $perfil = 'Nivelación focalizada según área';
                }

                return [
                    'id_post' => $postulante->id_post,
                    'nombre_completo' => trim(($postulante->nombres_post ?? '') . ' ' . ($postulante->apellidos_post ?? '')),
                    'edad' => $postulante->edad_post ?? 'No registrado',
                    'colegio' => $postulante->colegio ? $postulante->colegio->nombre_col : 'No registrado',
                    'universidad' => $universidad ? $universidad->nombre_uni : 'No registrado',
                    'carrera' => $carrera ? $carrera->nombre_car : 'No registrado',
                    'exigencia' => $exigencia,
                    'estado' => $postulante->estado_post ?? 'Activo',
                    'perfil' => $perfil,
                ];
            });

        return Inertia::render('Reportes/Index', [
            'metricas' => [
                'totalPostulantes' => $totalPostulantes,
                'totalColegios' => $totalColegios,
                'totalUniversidades' => $totalUniversidades,
                'totalCarreras' => $totalCarreras,
                'totalPreguntas' => $totalPreguntas,
                'totalPlantillas' => $totalPlantillas,
                'totalAreas' => $totalAreas,
                'totalTemas' => $totalTemas,
            ],
            'postulantesPorUniversidad' => $postulantesPorUniversidad,
            'postulantesPorCarrera' => $postulantesPorCarrera,
            'postulantesPorColegio' => $postulantesPorColegio,
            'preguntasPorArea' => $preguntasPorArea,
            'preguntasPorDificultad' => $preguntasPorDificultad,
            'postulantesPorExigenciaMatematica' => $postulantesPorExigenciaMatematica,
            'plantillasDetalle' => $plantillasDetalle,
            'postulantesList' => $postulantesList,
        ]);
    }
}
