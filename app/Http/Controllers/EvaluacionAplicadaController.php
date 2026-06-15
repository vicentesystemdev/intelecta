<?php

namespace App\Http\Controllers;

use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Actions\EnviarRespuestasEvaluacionAction;
use App\Domains\Resultados\Actions\IniciarEvaluacionAplicadaAction;
use App\Domains\Resultados\DTOs\EnviarEvaluacionData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Repositories\EvaluacionAplicadaRepository;
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
    public function index(
        Request $request,
        EvaluacionAplicadaRepository $repository,
    ): Response|RedirectResponse {
        if ($request->user()->hasAnyRole(['Super Administrador', 'Administrador', 'Docente'])) {
            return to_route('dashboard');
        }

        $postulante = $this->postulante($request);
        $habilitacion = $this->habilitacion($postulante);
        $schemaReady = Schema::hasTable('evaluaciones_aplicadas')
            && Schema::hasTable('respuestas_evaluacion');
        $evaluacion = null;
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
            $evaluacion = $evaluacion
                ? $repository->portalEvaluation($evaluacion)
                : null;
            $resultado = $resultadoModel
                ? $repository->result($resultadoModel)
                : null;
        }

        return Inertia::render('Estudiante/Evaluaciones', [
            'postulanteVinculado' => (bool) $postulante,
            'habilitacionAcademica' => $habilitacion,
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
    ): RedirectResponse {
        $this->ensureResultsSchema();
        $postulante = $this->requirePostulante($request);
        $this->ensureEnabled($postulante);
        $evaluacion = $action->execute(
            postulante: $postulante,
            plantilla: $plantilla,
            simulacroId: $request->validated('id_sim'),
            tipo: $request->validated('tipo_eval_apl'),
        );

        return to_route('estudiante.evaluaciones', [
            'evaluacion' => $evaluacion->id_eval_apl,
        ]);
    }

    public function enviar(
        EnviarRespuestasEvaluacionRequest $request,
        int $evaluacion,
        EnviarRespuestasEvaluacionAction $action,
    ): RedirectResponse {
        $this->ensureResultsSchema();
        $postulante = $this->requirePostulante($request);
        $this->ensureEnabled($postulante);
        $evaluacion = EvaluacionAplicada::findOrFail($evaluacion);
        $evaluacion = $action->execute(
            $evaluacion,
            $postulante,
            EnviarEvaluacionData::fromArray($request->validated()),
        );

        return to_route('estudiante.evaluaciones', [
            'resultado' => $evaluacion->id_eval_apl,
        ])->with('success', 'La evaluación fue finalizada y calificada correctamente.');
    }

    private function postulante(Request $request): ?Postulante
    {
        return Postulante::query()
            ->where('email_post', $request->user()->email)
            ->first();
    }

    private function requirePostulante(Request $request): Postulante
    {
        return $this->postulante($request) ?? throw ValidationException::withMessages([
            'postulante' => 'No existe un postulante vinculado al correo de la cuenta autenticada.',
        ]);
    }

    private function habilitacion(?Postulante $postulante): ?HabilitacionAcademica
    {
        if (! $postulante || ! Schema::hasTable('habilitaciones_academicas')) {
            return null;
        }

        return HabilitacionAcademica::query()
            ->where('id_post', $postulante->id_post)
            ->latest('id_hab')
            ->first([
                'estado_hab',
                'motivo_hab',
                'habilitado_evaluaciones_hab',
                'habilitado_simulacros_hab',
                'habilitado_reportes_hab',
            ]);
    }

    private function ensureEnabled(Postulante $postulante): void
    {
        $habilitacion = $this->habilitacion($postulante);

        if ($habilitacion?->habilitado_evaluaciones_hab === false) {
            throw ValidationException::withMessages([
                'habilitacion' => 'Tu acceso a evaluaciones se encuentra temporalmente restringido. Consulta con administración académica.',
            ]);
        }
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
