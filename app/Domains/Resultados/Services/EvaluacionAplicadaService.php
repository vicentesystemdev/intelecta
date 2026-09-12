<?php

namespace App\Domains\Resultados\Services;

use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Services\ContextoAcademicoService;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Services\ConsistenciaEvaluacionService;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\DTOs\EnviarEvaluacionData;
use App\Domains\Resultados\DTOs\EvaluacionAplicadaData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Resultados\Repositories\EvaluacionAplicadaRepository;
use App\Domains\Resultados\Repositories\RespuestaEvaluacionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluacionAplicadaService
{
    public function __construct(
        private readonly EvaluacionAplicadaRepository $evaluaciones,
        private readonly RespuestaEvaluacionRepository $respuestas,
        private readonly ResultadoAcademicoService $resultados,
        private readonly ContextoAcademicoService $contexto,
        private readonly ConsistenciaEvaluacionService $consistencia,
    ) {}

    public function iniciar(
        Postulante $postulante,
        PlantillaEvaluacion $plantilla,
        ?int $simulacroId = null,
        ?string $tipo = null,
        bool $historica = false,
    ): EvaluacionAplicada {
        return DB::transaction(function () use ($postulante, $plantilla, $simulacroId, $tipo, $historica) {
            // Orden global del contexto compartido: Simulacro -> Grupo -> Programa ->
            // Postulante -> Plantilla. Dentro de cada tipo se bloquea por PK ascendente.
            // Las operaciones sin alguno de estos recursos conservan el orden relativo.
            $simulacro = $simulacroId
                ? $this->bloquearContextoSimulacro($simulacroId, $plantilla, $historica)
                : null;

            $postulante = Postulante::query()
                ->whereKey($postulante->getKey())
                ->lockForUpdate()
                ->first();

            if (! $postulante || $postulante->estado_post !== 'activo') {
                throw ValidationException::withMessages([
                    'postulante' => 'El postulante no está habilitado para iniciar una evaluación.',
                ]);
            }

            $plantilla = PlantillaEvaluacion::query()
                ->whereKey($plantilla->getKey())
                ->lockForUpdate()
                ->first();

            if (! $plantilla) {
                throw ValidationException::withMessages([
                    'plantilla' => 'La plantilla seleccionada no está disponible para aplicación.',
                ]);
            }

            $preguntas = $this->consistencia->validarPlantillaAplicable($plantilla);

            if ($simulacro) {
                $this->validarInscripcionSimulacro($simulacro, $postulante);
            }

            $open = $this->evaluaciones->findOpen(
                $postulante->id_post,
                $plantilla->id_plan,
                $simulacroId,
            );
            if ($open) {
                return $open;
            }

            $maximo = round((float) $preguntas->sum(
                fn ($pregunta) => (float) ($pregunta->pivot?->puntaje_pp ?: $pregunta->puntaje_preg),
            ), 2);

            return $this->evaluaciones->create(new EvaluacionAplicadaData(
                postulanteId: $postulante->id_post,
                plantillaId: $plantilla->id_plan,
                simulacroId: $simulacroId,
                tipo: $tipo ?: $plantilla->dificultad_plan,
                puntajeMaximo: $maximo > 0 ? $maximo : 100,
            ));
        }, 3);
    }

    public function enviar(
        EvaluacionAplicada $evaluacion,
        Postulante $postulante,
        EnviarEvaluacionData $data,
    ): EvaluacionAplicada {
        return DB::transaction(function () use ($evaluacion, $postulante, $data) {
            $evaluacion = $this->evaluaciones->lock($evaluacion);

            if ($evaluacion->id_post !== $postulante->id_post) {
                throw ValidationException::withMessages([
                    'evaluacion' => 'La evaluación no pertenece al postulante autenticado.',
                ]);
            }

            if ($evaluacion->estado_eval_apl !== 'en_progreso') {
                throw ValidationException::withMessages([
                    'evaluacion' => 'La evaluación ya fue finalizada o no admite nuevas respuestas.',
                ]);
            }

            $plantilla = PlantillaEvaluacion::query()
                ->whereKey($evaluacion->id_plantilla)
                ->lockForUpdate()
                ->firstOrFail();

            $preguntas = $plantilla->preguntas()
                ->withTrashed()
                ->with(['tema.area.materia', 'alternativas'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id_preg');
            if ($plantilla->estado_plan !== 'activa'
                || $plantilla->trashed()
                || $preguntas->isEmpty()
                || $preguntas->contains(fn ($pregunta) => $pregunta->estado_preg !== 'activo' || $pregunta->trashed())) {
                throw ValidationException::withMessages([
                    'evaluacion' => 'La evaluación cambió y no puede finalizarse de forma consistente.',
                ]);
            }

            $maximoActual = round((float) $preguntas->sum(
                fn ($pregunta) => (float) ($pregunta->pivot?->puntaje_pp ?: $pregunta->puntaje_preg),
            ), 2);
            if (abs($maximoActual - (float) $evaluacion->puntaje_maximo_eval_apl) > 0.001) {
                throw ValidationException::withMessages([
                    'evaluacion' => 'La evaluación cambió y no puede finalizarse de forma consistente.',
                ]);
            }

            $enviadas = collect($data->respuestas)->keyBy('preguntaId');
            $invalidas = $enviadas->keys()->diff($preguntas->keys());

            if ($invalidas->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'respuestas' => 'Una o más preguntas no pertenecen a la plantilla aplicada.',
                ]);
            }

            foreach ($preguntas as $pregunta) {
                $respuesta = $enviadas->get($pregunta->id_preg);
                $alternativa = $respuesta?->alternativaId
                    ? $pregunta->alternativas->firstWhere('id_alt', $respuesta->alternativaId)
                    : null;

                if (
                    $respuesta?->alternativaId
                    && (! $alternativa || $alternativa->estado_alt !== 'activo')
                ) {
                    throw ValidationException::withMessages([
                        'respuestas' => 'Una alternativa seleccionada no pertenece a su pregunta o ya no está activa.',
                    ]);
                }
            }

            try {
                foreach ($preguntas as $pregunta) {
                    $this->consistencia->validarPreguntaAplicable($pregunta);
                }
            } catch (ValidationException) {
                throw ValidationException::withMessages([
                    'evaluacion' => 'La evaluación cambió y no puede finalizarse de forma consistente.',
                ]);
            }

            foreach ($preguntas as $pregunta) {
                $respuesta = $enviadas->get($pregunta->id_preg);
                $alternativa = $respuesta?->alternativaId
                    ? $pregunta->alternativas->firstWhere('id_alt', $respuesta->alternativaId)
                    : null;

                $puntajeMaximo = round(
                    (float) ($pregunta->pivot?->puntaje_pp ?: $pregunta->puntaje_preg),
                    2,
                );
                $correcta = (bool) ($alternativa?->es_correcta_alt ?? false);

                $this->respuestas->save($evaluacion, [
                    'id_eval_apl' => $evaluacion->id_eval_apl,
                    'id_preg' => $pregunta->id_preg,
                    'id_alt' => $alternativa?->id_alt,
                    'respuesta_texto_resp' => $respuesta?->respuestaTexto,
                    'es_correcta_resp' => $correcta,
                    'puntaje_obtenido_resp' => $correcta ? $puntajeMaximo : 0,
                    'puntaje_maximo_resp' => $puntajeMaximo,
                    'tiempo_segundos_resp' => $respuesta?->tiempoSegundos,
                    'intentos_resp' => $respuesta?->intentos ?? 1,
                    'orden_resp' => $pregunta->pivot?->orden_pp,
                ]);
            }

            $evaluacion = $this->resultados->recalculate($evaluacion);
            $evaluacion->update([
                'estado_eval_apl' => 'finalizada',
                'fecha_fin_eval_apl' => now(),
                'tiempo_total_segundos_eval_apl' => $data->tiempoTotalSegundos,
            ]);
            $evaluacion = $evaluacion->refresh();
            $this->resultados->updatePerformance($evaluacion);

            return $evaluacion;
        });
    }

    private function bloquearContextoSimulacro(
        int $simulacroId,
        PlantillaEvaluacion $plantilla,
        bool $historica,
    ): SimulacroProgramado {
        $simulacro = SimulacroProgramado::query()
            ->whereKey($simulacroId)
            ->lockForUpdate()
            ->first();

        if (! $simulacro || (! $historica && $simulacro->estado_sim !== 'programado')) {
            throw ValidationException::withMessages([
                'id_sim' => 'El simulacro no está disponible para iniciar un nuevo intento.',
            ]);
        }

        if ($simulacro->id_plantilla !== $plantilla->id_plan) {
            throw ValidationException::withMessages([
                'id_sim' => 'El simulacro no corresponde a la plantilla seleccionada.',
            ]);
        }

        if ($simulacro->id_grupo) {
            $this->contexto->grupoOperativo($simulacro->id_grupo, $simulacro->id_prog, true);
        }

        $this->contexto->programaOperativo($simulacro->id_prog, true);

        return $simulacro;
    }

    private function validarInscripcionSimulacro(
        SimulacroProgramado $simulacro,
        Postulante $postulante,
    ): void {

        $inscrito = InscripcionAcademica::query()
            ->where('id_post', $postulante->id_post)
            ->where('id_prog', $simulacro->id_prog)
            ->where('estado_inscripcion', 'activo')
            ->when(
                $simulacro->id_grupo,
                fn ($query, int $grupoId) => $query->where('id_grupo', $grupoId),
            )
            ->exists();

        if (! $inscrito) {
            throw ValidationException::withMessages([
                'id_sim' => 'El simulacro no pertenece al contexto académico activo del postulante.',
            ]);
        }
    }
}
