<?php

namespace App\Http\Controllers;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Reportes\Services\CoberturaCurricularService;
use App\Domains\Reportes\Services\ReporteExportacionService;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Exports\Reportes\ReporteAcademicoExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteAcademicoController extends Controller
{
    public function descargarPdf(
        string $tipo,
        ReporteExportacionService $reportes,
        BitacoraService $bitacora,
    )
    {
        $reporte = $reportes->obtenerReporte($tipo);
        $nombreReporte = $this->nombreReporte($tipo);

        $bitacora->registrar([
            'accion' => 'exportar_pdf',
            'modulo' => 'Reportes Académicos',
            'entidad' => 'reporte',
            'entidad_id' => $tipo,
            'descripcion' => "Se descargó el Reporte de {$nombreReporte} en formato PDF.",
            'valores_nuevos' => [
                'tipo' => $tipo,
                'formato' => 'pdf',
                'filas' => collect($reporte['filas'])->count(),
            ],
        ]);

        $pdf = Pdf::loadView('reportes.pdf.reporte', [
            'reporte' => $reporte,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($reportes->obtenerNombreArchivo($tipo, 'pdf'));
    }

    public function descargarExcel(
        string $tipo,
        ReporteExportacionService $reportes,
        BitacoraService $bitacora,
    ): BinaryFileResponse
    {
        $reporte = $reportes->obtenerReporte($tipo);
        $nombreReporte = $this->nombreReporte($tipo);

        $bitacora->registrar([
            'accion' => 'exportar_excel',
            'modulo' => 'Reportes Académicos',
            'entidad' => 'reporte',
            'entidad_id' => $tipo,
            'descripcion' => "Se descargó el Reporte de {$nombreReporte} en formato Excel.",
            'valores_nuevos' => [
                'tipo' => $tipo,
                'formato' => 'xlsx',
                'filas' => collect($reporte['filas'])->count(),
            ],
        ]);

        return Excel::download(
            new ReporteAcademicoExport($reporte),
            $reportes->obtenerNombreArchivo($tipo, 'xlsx'),
        );
    }

    private function nombreReporte(string $tipo): string
    {
        return match ($tipo) {
            'rendimiento' => 'Rendimiento Académico',
            'evaluaciones' => 'Evaluaciones Aplicadas',
            'asistencia' => 'Asistencia Académica',
            'habilitacion' => 'Habilitación Académica',
            default => 'Seguimiento Académico',
        };
    }

    public function index(Request $request, CoberturaCurricularService $cobertura): Response
    {
        // 1. KPIs principales
        $totalPostulantes = Postulante::count();
        $totalColegios = Colegio::count();
        $totalUniversidades = Universidad::count();
        $totalCarreras = Carrera::count();
        $totalPreguntas = Pregunta::count();
        $totalPlantillas = PlantillaEvaluacion::count();
        $totalPlantillasActivas = PlantillaEvaluacion::where('estado_plan', 'activa')->count();
        $totalMaterias = Materia::count();
        $totalAreas = AreaConocimiento::count();
        $totalTemas = Tema::count();
        $totalEvaluacionesAplicadas = 0;
        $promedioEvaluacionesAplicadas = 0;

        if (Schema::hasTable('evaluaciones_aplicadas')) {
            $totalEvaluacionesAplicadas = EvaluacionAplicada::where(
                'estado_eval_apl',
                'finalizada',
            )->count();
            $promedioEvaluacionesAplicadas = round(
                (float) EvaluacionAplicada::where(
                    'estado_eval_apl',
                    'finalizada',
                )->avg('porcentaje_eval_apl'),
                2,
            );
        }

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
            ->with('preguntas.tema.area.materia:id_mat,codigo_mat,nombre_mat')
            ->get()
            ->map(function ($plantilla) {
                $materias = $plantilla->preguntas
                    ->groupBy(fn ($pregunta) => $pregunta->tema?->area?->materia?->nombre_mat ?? 'Sin materia')
                    ->map(fn ($preguntas, $materia) => [
                        'materia' => $materia,
                        'cantidad' => $preguntas->count(),
                    ])
                    ->values();

                return [
                    'id_plan' => $plantilla->id_plan,
                    'nombre_plan' => $plantilla->nombre_plan,
                    'dificultad_plan' => $plantilla->dificultad_plan,
                    'cantidad_preguntas' => $plantilla->preguntas_count,
                    'puntaje_total' => (float) $plantilla->preguntas()->sum('plantilla_preguntas.puntaje_pp'),
                    'estado_plan' => $plantilla->estado_plan,
                    'materias' => $materias,
                ];
            });

        // 9. Reporte individual por postulante
        $postulantesQuery = Postulante::with(['colegio', 'carrera.universidad']);

        if (Schema::hasTable('evaluaciones_aplicadas')) {
            $postulantesQuery
                ->withCount([
                    'evaluacionesAplicadas as evaluaciones_finalizadas_count' => fn ($query) => $query
                        ->where('estado_eval_apl', 'finalizada'),
                ])
                ->withAvg([
                    'evaluacionesAplicadas as promedio_evaluaciones' => fn ($query) => $query
                        ->where('estado_eval_apl', 'finalizada'),
                ], 'porcentaje_eval_apl');
        }

        $postulantesList = $postulantesQuery->get()
            ->map(function ($postulante) {
                $carrera = $postulante->carrera;
                $universidad = $carrera ? $carrera->universidad : null;
                $exigencia = $carrera?->nivel_exigencia_matematica_car ?: 'Sin clasificación';
                $exigenciaNormalizada = mb_strtolower($exigencia);

                if (in_array($exigenciaNormalizada, ['alta', 'media-alta'], true)) {
                    $perfil = 'Evaluación diagnóstica prioritaria';
                } elseif ($exigenciaNormalizada === 'media') {
                    $perfil = 'Seguimiento académico sugerido';
                } else {
                    $perfil = 'Perfil académico por verificar';
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
                    'evaluaciones_finalizadas' => (int) ($postulante->evaluaciones_finalizadas_count ?? 0),
                    'promedio_evaluaciones' => $postulante->promedio_evaluaciones !== null
                        ? round((float) $postulante->promedio_evaluaciones, 2)
                        : null,
                    'lectura_materias' => ($postulante->evaluaciones_finalizadas_count ?? 0) > 0
                        ? sprintf(
                            '%d evaluación(es) aplicada(s) con promedio general de %.2f%%.',
                            $postulante->evaluaciones_finalizadas_count,
                            $postulante->promedio_evaluaciones,
                        )
                        : 'Sin evaluaciones aplicadas registradas.',
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
                'totalPlantillasActivas' => $totalPlantillasActivas,
                'totalMaterias' => $totalMaterias,
                'totalAreas' => $totalAreas,
                'totalTemas' => $totalTemas,
                'totalEvaluacionesAplicadas' => $totalEvaluacionesAplicadas,
                'promedioEvaluacionesAplicadas' => $promedioEvaluacionesAplicadas,
            ],
            'postulantesPorUniversidad' => $postulantesPorUniversidad,
            'postulantesPorCarrera' => $postulantesPorCarrera,
            'postulantesPorColegio' => $postulantesPorColegio,
            'preguntasPorArea' => $preguntasPorArea,
            'preguntasPorDificultad' => $preguntasPorDificultad,
            'coberturaMaterias' => $cobertura->resumenMaterias(),
            'dificultadPorMateria' => $cobertura->dificultadPorMateria(),
            'plantillasPorTipo' => $cobertura->plantillasPorTipo(),
            'preguntasSinMateria' => $cobertura->preguntasSinMateria(),
            'postulantesPorExigenciaMatematica' => $postulantesPorExigenciaMatematica,
            'plantillasDetalle' => $plantillasDetalle,
            'postulantesList' => $postulantesList,
        ]);
    }
}
