<?php

namespace App\Domains\Resultados\Services;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
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
    ) {}

    public function iniciar(
        Postulante $postulante,
        PlantillaEvaluacion $plantilla,
        ?int $simulacroId = null,
        ?string $tipo = null,
    ): EvaluacionAplicada {
        if ($plantilla->estado_plan !== 'activa') {
            throw ValidationException::withMessages([
                'plantilla' => 'La plantilla seleccionada no está disponible para aplicación.',
            ]);
        }

        $plantilla->load('preguntas');

        if ($plantilla->preguntas->isEmpty()) {
            throw ValidationException::withMessages([
                'plantilla' => 'La plantilla seleccionada no tiene preguntas evaluables.',
            ]);
        }

        return DB::transaction(function () use ($postulante, $plantilla, $simulacroId, $tipo) {
            $open = $this->evaluaciones->findOpen($postulante->id_post, $plantilla->id_plan);
            if ($open) {
                return $open;
            }

            $maximo = round((float) $plantilla->preguntas->sum(
                fn ($pregunta) => (float) ($pregunta->pivot?->puntaje_pp ?: $pregunta->puntaje_preg),
            ), 2);

            return $this->evaluaciones->create(new EvaluacionAplicadaData(
                postulanteId: $postulante->id_post,
                plantillaId: $plantilla->id_plan,
                simulacroId: $simulacroId,
                tipo: $tipo ?: $plantilla->dificultad_plan,
                puntajeMaximo: $maximo > 0 ? $maximo : 100,
            ));
        });
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

            $evaluacion->load([
                'plantilla.preguntas.alternativas',
            ]);
            $preguntas = $evaluacion->plantilla->preguntas->keyBy('id_preg');
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

                if ($respuesta?->alternativaId && ! $alternativa) {
                    throw ValidationException::withMessages([
                        'respuestas' => 'Una alternativa seleccionada no pertenece a su pregunta.',
                    ]);
                }

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
}
