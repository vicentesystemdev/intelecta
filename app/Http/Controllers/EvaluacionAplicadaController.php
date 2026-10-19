<?php

namespace App\Http\Controllers;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Resultados\Actions\EnviarRespuestasEvaluacionAction;
use App\Domains\Resultados\Actions\IniciarEvaluacionAplicadaAction;
use App\Domains\Resultados\DTOs\EnviarEvaluacionData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Repositories\EvaluacionAplicadaRepository;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Http\Requests\Resultados\EnviarRespuestasEvaluacionRequest;
use App\Http\Requests\Resultados\IniciarEvaluacionAplicadaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EvaluacionAplicadaController extends Controller
{
    public function __construct(private readonly AccesoEvaluacionService $acceso) {}

    public function index(
        Request $request,
        EvaluacionAplicadaRepository $repository,
    ): Response {
        $postulante = $request->user()->postulante;
        $schemaReady = Schema::hasTable('evaluaciones_aplicadas')
            && Schema::hasTable('respuestas_evaluacion');
        $evaluacion = null;
        $simulacroEvaluacion = null;
        $resultado = null;
        $historial = collect();

        if ($schemaReady && $postulante) {
            $evaluacion = EvaluacionAplicada::query()
                ->where('id_post', $postulante->id_post)
                ->where('estado_eval_apl', 'en_progreso')
                ->when(
                    $request->integer('evaluacion'),
                    fn ($query, int $id) => $query->where('id_eval_apl', $id),
                )
                ->latest('id_eval_apl')
                ->first();

            $resultadoModel = EvaluacionAplicada::query()
                ->where('id_post', $postulante->id_post)
                ->where('estado_eval_apl', 'finalizada')
                ->when(
                    $request->integer('resultado'),
                    fn ($query, int $id) => $query->where('id_eval_apl', $id),
                    fn ($query) => $query->whereRaw('1 = 0'),
                )
                ->first();

            $historial = $repository->recentForPostulante($postulante->id_post);
            $simulacroEvaluacion = $evaluacion?->simulacro;
            $evaluacion = $evaluacion
                ? $repository->portalEvaluation($evaluacion)
                : null;
            $resultado = $resultadoModel
                ? $repository->result($resultadoModel)
                : null;
        }

        $decision = $this->acceso->evaluar($postulante, $simulacroEvaluacion);

        return Inertia::render('Estudiante/Evaluaciones', [
            'postulanteVinculado' => (bool) $postulante,
            'habilitacionAcademica' => $decision['habilitacion'],
            'accesoEvaluaciones' => ['permitido' => $decision['permitido'], 'motivo' => $decision['motivo']],
            'estructuraResultadosDisponible' => $schemaReady,
            'plantillas' => $repository->availableTemplates(),
            'evaluacionActiva' => $evaluacion,
            'resultado' => $resultado,
            'historial' => $historial,
        ]);
    }

    public function iniciar(
        IniciarEvaluacionAplicadaRequest $request,
        PlantillaEvaluacion $plantilla,
        IniciarEvaluacionAplicadaAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $this->ensureResultsSchema();
        $postulante = $request->user()->postulante;
        $evaluacion = $action->execute(
            postulante: $postulante,
            plantilla: $plantilla,
            simulacroId: $request->validated('id_sim'),
            tipo: $request->validated('tipo_eval_apl'),
        );

        $bitacora->registrar([
            'accion' => 'iniciar_evaluacion',
            'modulo' => 'Evaluaciones Aplicadas',
            'entidad' => 'evaluaciones_aplicadas',
            'entidad_id' => $evaluacion->id_eval_apl,
            'descripcion' => 'Un postulante inició una evaluación aplicada.',
            'valores_nuevos' => [
                'id_post' => $postulante->id_post,
                'id_plantilla' => $plantilla->id_plan,
                'estado_eval_apl' => $evaluacion->estado_eval_apl,
            ],
        ]);

        return to_route('estudiante.evaluaciones', [
            'evaluacion' => $evaluacion->id_eval_apl,
        ]);
    }

    public function enviar(
        EnviarRespuestasEvaluacionRequest $request,
        int $evaluacion,
        EnviarRespuestasEvaluacionAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $this->ensureResultsSchema();
        $postulante = $request->user()->postulante;
        if (! $postulante) {
            $this->acceso->validar(null);
        }
        $evaluacion = $postulante->evaluacionesAplicadas()->findOrFail($evaluacion);
        $this->acceso->validar($postulante, $evaluacion->simulacro);
        $evaluacion = $action->execute(
            $evaluacion,
            $postulante,
            EnviarEvaluacionData::fromArray($request->validated()),
        );

        $bitacora->registrar([
            'accion' => 'finalizar_evaluacion',
            'modulo' => 'Evaluaciones Aplicadas',
            'entidad' => 'evaluaciones_aplicadas',
            'entidad_id' => $evaluacion->id_eval_apl,
            'descripcion' => 'Un postulante finalizó y envió respuestas de una evaluación aplicada.',
            'valores_nuevos' => [
                'id_post' => $postulante->id_post,
                'estado_eval_apl' => $evaluacion->estado_eval_apl,
                'puntaje_total_eval_apl' => $evaluacion->puntaje_total_eval_apl,
                'puntaje_maximo_eval_apl' => $evaluacion->puntaje_maximo_eval_apl,
                'porcentaje_eval_apl' => $evaluacion->porcentaje_eval_apl,
            ],
        ]);

        return to_route('estudiante.evaluaciones', [
            'resultado' => $evaluacion->id_eval_apl,
        ])->with('success', 'La evaluación fue finalizada y calificada correctamente.');
    }

    private function ensureResultsSchema(): void
    {
        if (
            ! Schema::hasTable('evaluaciones_aplicadas')
            || ! Schema::hasTable('respuestas_evaluacion')
        ) {
            throw ValidationException::withMessages([
                'evaluacion' => 'La estructura de resultados académicos todavía no está habilitada.',
            ]);
        }
    }
}
