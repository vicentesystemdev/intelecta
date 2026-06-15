<?php

namespace App\Http\Controllers;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Repositories\EvaluacionAplicadaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ResultadoAcademicoController extends Controller
{
    public function index(
        Request $request,
        EvaluacionAplicadaRepository $repository,
    ): Response {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_plantilla' => ['nullable', 'integer', 'exists:plantillas_evaluacion,id_plan'],
            'estado_eval_apl' => ['nullable', 'in:en_progreso,finalizada,anulada'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ], [
            'buscar.max' => 'La búsqueda no puede superar los 160 caracteres.',
            'id_plantilla.exists' => 'La plantilla seleccionada no existe.',
            'estado_eval_apl.in' => 'Seleccione un estado válido.',
            'fecha_hasta.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
        ]);
        $filters['estado_eval_apl'] ??= 'finalizada';

        $schemaReady = Schema::hasTable('evaluaciones_aplicadas')
            && Schema::hasTable('respuestas_evaluacion');
        $resultados = $schemaReady
            ? $repository->paginateResults($filters)
            : new LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        return Inertia::render('Modulos/ResultadosSeguimiento', [
            'resultados' => $resultados,
            'plantillas' => PlantillaEvaluacion::query()
                ->orderBy('nombre_plan')
                ->get(['id_plan', 'nombre_plan']),
            'filtros' => $filters,
            'estructuraResultadosDisponible' => $schemaReady,
            'metricas' => [
                'total' => $schemaReady ? EvaluacionAplicada::count() : 0,
                'finalizadas' => $schemaReady
                    ? EvaluacionAplicada::where('estado_eval_apl', 'finalizada')->count()
                    : 0,
                'enProgreso' => $schemaReady
                    ? EvaluacionAplicada::where('estado_eval_apl', 'en_progreso')->count()
                    : 0,
                'promedio' => $schemaReady
                    ? round((float) EvaluacionAplicada::where(
                        'estado_eval_apl',
                        'finalizada',
                    )->avg('porcentaje_eval_apl'), 2)
                    : 0,
            ],
        ]);
    }
}
