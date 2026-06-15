<?php

namespace App\Domains\Resultados\Repositories;

use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;

class ResultadoAcademicoRepository
{
    public function updatePerformance(Postulante $postulante): ?RendimientoPostulante
    {
        $evaluaciones = EvaluacionAplicada::query()
            ->with('respuestas.pregunta.tema.area.materia')
            ->where('id_post', $postulante->id_post)
            ->where('estado_eval_apl', 'finalizada')
            ->get();

        if ($evaluaciones->isEmpty()) {
            return null;
        }

        $inscripcion = $postulante->inscripcionesAcademicas()
            ->latest('fecha_inscripcion')
            ->latest('id_insc')
            ->first();

        $query = RendimientoPostulante::query()
            ->where('id_post', $postulante->id_post);

        $inscripcion?->id_prog
            ? $query->where('id_prog', $inscripcion->id_prog)
            : $query->whereNull('id_prog');

        $rendimiento = $query->first() ?? new RendimientoPostulante([
            'id_post' => $postulante->id_post,
            'id_prog' => $inscripcion?->id_prog,
        ]);

        $promedioGeneral = round((float) $evaluaciones->avg('porcentaje_eval_apl'), 2);
        $materias = $this->subjectAverages($evaluaciones);

        $rendimiento->fill([
            'id_grupo' => $inscripcion?->id_grupo,
            'promedio_general_rend' => $promedioGeneral,
            'promedio_matematica_rend' => $materias['MAT'] ?? null,
            'promedio_fisica_rend' => $materias['FIS'] ?? null,
            'promedio_quimica_rend' => $materias['QMC'] ?? null,
            'promedio_paa_rend' => $materias['PAA'] ?? null,
            'nivel_riesgo_rend' => match (true) {
                $promedioGeneral >= 80 => 'Alto rendimiento',
                $promedioGeneral >= 65 => 'Seguimiento regular',
                default => 'Atención prioritaria',
            },
            'observacion_rend' => sprintf(
                'Promedio recalculado desde %d evaluación(es) aplicada(s) finalizada(s).',
                $evaluaciones->count(),
            ),
        ]);
        $rendimiento->save();

        return $rendimiento->refresh();
    }

    private function subjectAverages($evaluaciones): array
    {
        $totals = [];

        foreach ($evaluaciones as $evaluacion) {
            foreach ($evaluacion->respuestas as $respuesta) {
                $code = $respuesta->pregunta?->tema?->area?->materia?->codigo_mat;
                if (! $code) {
                    continue;
                }

                $totals[$code] ??= ['obtenido' => 0.0, 'maximo' => 0.0];
                $totals[$code]['obtenido'] += (float) $respuesta->puntaje_obtenido_resp;
                $totals[$code]['maximo'] += (float) $respuesta->puntaje_maximo_resp;
            }
        }

        return collect($totals)
            ->map(fn (array $total) => $total['maximo'] > 0
                ? round(($total['obtenido'] / $total['maximo']) * 100, 2)
                : null)
            ->filter(fn ($value) => $value !== null)
            ->all();
    }
}
