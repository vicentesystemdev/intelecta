<?php

namespace App\Http\Controllers;

use App\Domains\Resultados\Repositories\EvaluacionAplicadaRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ? $repository->paginateResults($filters, $request->user())
            : new LengthAwarePaginator([], 0, 15, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        if ($schemaReady && isset($filters['id_plantilla'])) {
            abort_unless(
                $repository->plantillaPerteneceAlAmbito($request->user(), (int) $filters['id_plantilla']),
                403,
            );
        }

        return Inertia::render('Modulos/ResultadosSeguimiento', [
            'resultados' => $resultados,
            'plantillas' => $schemaReady
                ? $repository->plantillasResultsOptions($request->user())
                : [],
            'filtros' => $filters,
            'estructuraResultadosDisponible' => $schemaReady,
            'metricas' => $schemaReady
                ? $repository->resultMetrics($request->user())
                : ['total' => 0, 'finalizadas' => 0, 'enProgreso' => 0, 'promedio' => 0],
        ]);
    }
}
