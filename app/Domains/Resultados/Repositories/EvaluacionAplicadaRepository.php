<?php

namespace App\Domains\Resultados\Repositories;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\DTOs\EvaluacionAplicadaData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EvaluacionAplicadaRepository
{
    public function findOpen(int $postulanteId, int $plantillaId): ?EvaluacionAplicada
    {
        return EvaluacionAplicada::query()
            ->where('id_post', $postulanteId)
            ->where('id_plantilla', $plantillaId)
            ->where('estado_eval_apl', 'en_progreso')
            ->latest('id_eval_apl')
            ->lockForUpdate()
            ->first();
    }

    public function create(EvaluacionAplicadaData $data): EvaluacionAplicada
    {
        $evaluacion = EvaluacionAplicada::create($data->toArray());
        $evaluacion->update([
            'codigo_eval_apl' => sprintf(
                'EVA-%s-%06d',
                now()->format('Y'),
                $evaluacion->id_eval_apl,
            ),
        ]);

        return $evaluacion->refresh();
    }

    public function lock(EvaluacionAplicada $evaluacion): EvaluacionAplicada
    {
        return EvaluacionAplicada::query()
            ->whereKey($evaluacion->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function availableTemplates(): Collection
    {
        return PlantillaEvaluacion::query()
            ->where('estado_plan', 'activa')
            ->whereHas('preguntas')
            ->withCount('preguntas')
            ->withSum('preguntas as puntaje_maximo', 'plantilla_preguntas.puntaje_pp')
            ->with('preguntas.tema.area.materia:id_mat,codigo_mat,nombre_mat')
            ->orderBy('nombre_plan')
            ->get()
            ->map(fn (PlantillaEvaluacion $plantilla) => [
                'id_plan' => $plantilla->id_plan,
                'nombre_plan' => $plantilla->nombre_plan,
                'descripcion_plan' => $plantilla->descripcion_plan,
                'objetivo_plan' => $plantilla->objetivo_plan,
                'duracion_minutos_plan' => $plantilla->duracion_minutos_plan,
                'dificultad_plan' => $plantilla->dificultad_plan,
                'preguntas_count' => $plantilla->preguntas_count,
                'puntaje_maximo' => round((float) ($plantilla->puntaje_maximo ?: 100), 2),
                'materias' => $plantilla->preguntas
                    ->pluck('tema.area.materia.nombre_mat')
                    ->filter()
                    ->unique()
                    ->values(),
            ]);
    }

    public function portalEvaluation(EvaluacionAplicada $evaluacion): array
    {
        $evaluacion->load([
            'plantilla.preguntas.tema.area.materia',
            'plantilla.preguntas.alternativas' => fn ($query) => $query
                ->where('estado_alt', 'activo')
                ->orderBy('orden_alt'),
        ]);

        return [
            'id_eval_apl' => $evaluacion->id_eval_apl,
            'codigo_eval_apl' => $evaluacion->codigo_eval_apl,
            'fecha_inicio_eval_apl' => $evaluacion->fecha_inicio_eval_apl,
            'estado_eval_apl' => $evaluacion->estado_eval_apl,
            'segundos_restantes' => max(
                0,
                ((int) ($evaluacion->plantilla->duracion_minutos_plan ?: 60) * 60)
                    - (int) $evaluacion->fecha_inicio_eval_apl?->diffInSeconds(now()),
            ),
            'plantilla' => [
                'id_plan' => $evaluacion->plantilla->id_plan,
                'nombre_plan' => $evaluacion->plantilla->nombre_plan,
                'descripcion_plan' => $evaluacion->plantilla->descripcion_plan,
                'duracion_minutos_plan' => $evaluacion->plantilla->duracion_minutos_plan,
            ],
            'preguntas' => $evaluacion->plantilla->preguntas->map(
                fn ($pregunta) => [
                    'id_preg' => $pregunta->id_preg,
                    'enunciado_preg' => $pregunta->enunciado_preg,
                    'tipo_preg' => $pregunta->tipo_preg,
                    'dificultad_preg' => $pregunta->dificultad_preg,
                    'materia' => $pregunta->tema?->area?->materia?->nombre_mat ?? 'Sin materia',
                    'area' => $pregunta->tema?->area?->nombre_area ?? 'Sin área',
                    'tema' => $pregunta->tema?->nombre_tem ?? 'Sin clasificación',
                    'orden' => $pregunta->pivot?->orden_pp,
                    'puntaje' => (float) ($pregunta->pivot?->puntaje_pp ?: $pregunta->puntaje_preg),
                    'alternativas' => $pregunta->alternativas->map(fn ($alternativa) => [
                        'id_alt' => $alternativa->id_alt,
                        'letra_alt' => $alternativa->letra_alt,
                        'texto_alt' => $alternativa->texto_alt,
                    ])->values(),
                ],
            )->values(),
        ];
    }

    public function result(EvaluacionAplicada $evaluacion): array
    {
        $evaluacion->load([
            'plantilla:id_plan,nombre_plan',
            'respuestas.alternativa:id_alt,letra_alt,texto_alt',
            'respuestas.pregunta:id_preg,enunciado_preg,explicacion_preg,id_tem',
            'respuestas.pregunta.tema.area.materia',
        ]);

        return [
            'id_eval_apl' => $evaluacion->id_eval_apl,
            'codigo_eval_apl' => $evaluacion->codigo_eval_apl,
            'plantilla' => $evaluacion->plantilla,
            'puntaje_total' => (float) $evaluacion->puntaje_total_eval_apl,
            'puntaje_maximo' => (float) $evaluacion->puntaje_maximo_eval_apl,
            'porcentaje' => (float) $evaluacion->porcentaje_eval_apl,
            'fecha_fin' => $evaluacion->fecha_fin_eval_apl,
            'tiempo_total_segundos' => $evaluacion->tiempo_total_segundos_eval_apl,
            'correctas' => $evaluacion->respuestas->where('es_correcta_resp', true)->count(),
            'total_preguntas' => $evaluacion->respuestas->count(),
            'respuestas' => $evaluacion->respuestas->map(fn ($respuesta) => [
                'id_resp_eval' => $respuesta->id_resp_eval,
                'enunciado' => $respuesta->pregunta?->enunciado_preg,
                'explicacion' => $respuesta->pregunta?->explicacion_preg,
                'materia' => $respuesta->pregunta?->tema?->area?->materia?->nombre_mat ?? 'Sin materia',
                'area' => $respuesta->pregunta?->tema?->area?->nombre_area ?? 'Sin área',
                'alternativa' => $respuesta->alternativa,
                'es_correcta' => $respuesta->es_correcta_resp,
                'puntaje_obtenido' => (float) $respuesta->puntaje_obtenido_resp,
                'puntaje_maximo' => (float) $respuesta->puntaje_maximo_resp,
            ])->values(),
        ];
    }

    public function recentForPostulante(int $postulanteId, int $limit = 8): Collection
    {
        return EvaluacionAplicada::query()
            ->with('plantilla:id_plan,nombre_plan')
            ->where('id_post', $postulanteId)
            ->where('estado_eval_apl', 'finalizada')
            ->latest('fecha_fin_eval_apl')
            ->limit($limit)
            ->get();
    }

    public function paginateResults(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return EvaluacionAplicada::query()
            ->with([
                'postulante:id_post,nombres_post,apellidos_post,ci_post',
                'plantilla:id_plan,nombre_plan',
                'simulacro:id_sim,titulo_sim',
            ])
            ->withCount([
                'respuestas',
                'respuestas as respuestas_correctas_count' => fn (Builder $query) => $query
                    ->where('es_correcta_resp', true),
            ])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower(trim($search)).'%';
                $query->whereHas('postulante', fn (Builder $query) => $query
                    ->whereRaw("LOWER(CONCAT_WS(' ', nombres_post, apellidos_post)) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(ci_post, '')) LIKE ?", [$pattern]));
            })
            ->when(
                $filters['id_plantilla'] ?? null,
                fn (Builder $query, int|string $value) => $query->where('id_plantilla', $value),
            )
            ->when(
                $filters['estado_eval_apl'] ?? null,
                fn (Builder $query, string $value) => $query->where('estado_eval_apl', $value),
            )
            ->when(
                $filters['fecha_desde'] ?? null,
                fn (Builder $query, string $value) => $query->whereDate('fecha_fin_eval_apl', '>=', $value),
            )
            ->when(
                $filters['fecha_hasta'] ?? null,
                fn (Builder $query, string $value) => $query->whereDate('fecha_fin_eval_apl', '<=', $value),
            )
            ->latest('fecha_fin_eval_apl')
            ->latest('id_eval_apl')
            ->paginate($perPage)
            ->withQueryString();
    }
}
