<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Actions\EnviarRespuestasEvaluacionAction;
use App\Domains\Resultados\Actions\IniciarEvaluacionAplicadaAction;
use App\Domains\Resultados\DTOs\EnviarEvaluacionData;
use App\Domains\Resultados\DTOs\RespuestaEvaluacionData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use Illuminate\Database\Seeder;

class EvaluacionesAplicadasSeeder extends Seeder
{
    public function run(): void
    {
        $postulantes = Postulante::query()
            ->where('estado_post', 'activo')
            ->orderBy('id_post')
            ->limit(8)
            ->get();
        $plantillas = PlantillaEvaluacion::query()
            ->where('estado_plan', 'activa')
            ->whereHas('preguntas.alternativas')
            ->with('preguntas.alternativas')
            ->orderBy('id_plan')
            ->limit(5)
            ->get();

        if ($postulantes->isEmpty() || $plantillas->isEmpty()) {
            return;
        }

        $iniciar = app(IniciarEvaluacionAplicadaAction::class);
        $enviar = app(EnviarRespuestasEvaluacionAction::class);

        foreach ($postulantes as $index => $postulante) {
            $plantilla = $plantillas[$index % $plantillas->count()];
            $exists = EvaluacionAplicada::query()
                ->where('id_post', $postulante->id_post)
                ->where('id_plantilla', $plantilla->id_plan)
                ->where('estado_eval_apl', 'finalizada')
                ->exists();

            if ($exists) {
                continue;
            }

            $evaluacion = $iniciar->execute(
                $postulante,
                $plantilla,
                tipo: 'evaluacion_institucional',
            );
            $respuestas = $plantilla->preguntas->values()->map(
                function ($pregunta, int $questionIndex) use ($index) {
                    $correcta = $pregunta->alternativas
                        ->firstWhere('es_correcta_alt', true);
                    $incorrecta = $pregunta->alternativas
                        ->firstWhere('es_correcta_alt', false);
                    $useCorrect = ($index + $questionIndex) % 4 !== 0;
                    $seleccionada = $useCorrect ? $correcta : ($incorrecta ?: $correcta);

                    return new RespuestaEvaluacionData(
                        preguntaId: $pregunta->id_preg,
                        alternativaId: $seleccionada?->id_alt,
                        respuestaTexto: null,
                        tiempoSegundos: 45 + (($questionIndex % 4) * 15),
                    );
                },
            )->all();

            $enviar->execute(
                $evaluacion,
                $postulante,
                new EnviarEvaluacionData(
                    respuestas: $respuestas,
                    tiempoTotalSegundos: collect($respuestas)->sum('tiempoSegundos'),
                ),
            );
        }
    }
}
