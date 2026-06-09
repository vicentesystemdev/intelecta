<?php

namespace App\Domains\Reportes\Services;

use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CoberturaCurricularService
{
    public function resumenMaterias(): Collection
    {
        $materias = Materia::query()
            ->orderBy('id_mat')
            ->get(['id_mat', 'codigo_mat', 'nombre_mat', 'color_mat', 'estado_mat']);

        $areas = AreaConocimiento::query()
            ->selectRaw('id_mat, COUNT(*) as total')
            ->whereNotNull('id_mat')
            ->groupBy('id_mat')
            ->pluck('total', 'id_mat');

        $temas = Tema::query()
            ->join('areas_conocimiento', 'temas.id_area', '=', 'areas_conocimiento.id_area')
            ->selectRaw('areas_conocimiento.id_mat, COUNT(temas.id_tem) as total')
            ->whereNotNull('areas_conocimiento.id_mat')
            ->groupBy('areas_conocimiento.id_mat')
            ->pluck('total', 'areas_conocimiento.id_mat');

        $preguntas = Pregunta::query()
            ->join('temas', 'preguntas.id_tem', '=', 'temas.id_tem')
            ->join('areas_conocimiento', 'temas.id_area', '=', 'areas_conocimiento.id_area')
            ->selectRaw('areas_conocimiento.id_mat, COUNT(preguntas.id_preg) as total')
            ->whereNotNull('areas_conocimiento.id_mat')
            ->groupBy('areas_conocimiento.id_mat')
            ->pluck('total', 'areas_conocimiento.id_mat');

        $plantillas = PlantillaEvaluacion::query()
            ->join('plantilla_preguntas', 'plantillas_evaluacion.id_plan', '=', 'plantilla_preguntas.id_plan')
            ->join('preguntas', 'plantilla_preguntas.id_preg', '=', 'preguntas.id_preg')
            ->join('temas', 'preguntas.id_tem', '=', 'temas.id_tem')
            ->join('areas_conocimiento', 'temas.id_area', '=', 'areas_conocimiento.id_area')
            ->selectRaw('areas_conocimiento.id_mat, COUNT(DISTINCT plantillas_evaluacion.id_plan) as total')
            ->whereNull('preguntas.deleted_at')
            ->whereNotNull('areas_conocimiento.id_mat')
            ->groupBy('areas_conocimiento.id_mat')
            ->pluck('total', 'areas_conocimiento.id_mat');

        $totalPreguntasAsignadas = max(1, (int) $preguntas->sum());

        return $materias->map(function (Materia $materia) use ($areas, $temas, $preguntas, $plantillas, $totalPreguntasAsignadas) {
            $cantidadAreas = (int) ($areas[$materia->id_mat] ?? 0);
            $cantidadTemas = (int) ($temas[$materia->id_mat] ?? 0);
            $cantidadPreguntas = (int) ($preguntas[$materia->id_mat] ?? 0);
            $cantidadPlantillas = (int) ($plantillas[$materia->id_mat] ?? 0);

            return [
                'id_mat' => $materia->id_mat,
                'codigo_mat' => $materia->codigo_mat,
                'nombre_mat' => $materia->nombre_mat,
                'color_mat' => $materia->color_mat ?: 'indigo',
                'estado_mat' => $materia->estado_mat,
                'areas' => $cantidadAreas,
                'temas' => $cantidadTemas,
                'preguntas' => $cantidadPreguntas,
                'plantillas' => $cantidadPlantillas,
                'participacion_banco' => round(($cantidadPreguntas / $totalPreguntasAsignadas) * 100, 1),
                'estado_cobertura' => $this->estadoCobertura(
                    $cantidadTemas,
                    $cantidadPreguntas,
                    $cantidadPlantillas,
                ),
            ];
        });
    }

    public function dificultadPorMateria(): Collection
    {
        $resumen = $this->resumenMaterias()->mapWithKeys(fn (array $materia) => [
            $materia['id_mat'] => [
                'id_mat' => $materia['id_mat'],
                'codigo_mat' => $materia['codigo_mat'],
                'nombre_mat' => $materia['nombre_mat'],
                'basica' => 0,
                'media' => 0,
                'avanzada' => 0,
                'sin_clasificacion' => 0,
            ],
        ]);

        Pregunta::query()
            ->join('temas', 'preguntas.id_tem', '=', 'temas.id_tem')
            ->join('areas_conocimiento', 'temas.id_area', '=', 'areas_conocimiento.id_area')
            ->selectRaw('areas_conocimiento.id_mat, preguntas.dificultad_preg, COUNT(preguntas.id_preg) as total')
            ->whereNotNull('areas_conocimiento.id_mat')
            ->groupBy('areas_conocimiento.id_mat', 'preguntas.dificultad_preg')
            ->get()
            ->each(function ($fila) use ($resumen) {
                if (! $resumen->has($fila->id_mat)) {
                    return;
                }

                $materia = $resumen->get($fila->id_mat);
                $clave = in_array($fila->dificultad_preg, ['basica', 'media', 'avanzada'], true)
                    ? $fila->dificultad_preg
                    : 'sin_clasificacion';
                $materia[$clave] = (int) $fila->total;
                $resumen->put($fila->id_mat, $materia);
            });

        return $resumen->values();
    }

    public function plantillasPorTipo(): Collection
    {
        $tipos = collect([
            'Diagnóstica' => 0,
            'Matemática' => 0,
            'Física' => 0,
            'Química' => 0,
            'Mixta' => 0,
            'Final Preuniversitaria' => 0,
            'PAA / Razonamiento' => 0,
        ]);

        PlantillaEvaluacion::query()
            ->with('preguntas.tema.area.materia:id_mat,codigo_mat,nombre_mat')
            ->get()
            ->each(function (PlantillaEvaluacion $plantilla) use ($tipos) {
                $tipo = $this->clasificarPlantilla($plantilla);
                $tipos->put($tipo, ((int) $tipos->get($tipo, 0)) + 1);
            });

        return $tipos
            ->filter(fn (int $total) => $total > 0)
            ->map(fn (int $total, string $tipo) => ['tipo' => $tipo, 'total' => $total])
            ->values();
    }

    public function preguntasSinMateria(): int
    {
        return Pregunta::query()
            ->whereDoesntHave('tema.area.materia')
            ->count();
    }

    private function estadoCobertura(int $temas, int $preguntas, int $plantillas): string
    {
        if ($temas >= 5 && $preguntas >= 20 && $plantillas >= 2) {
            return 'Consolidada';
        }

        if ($preguntas > 0 || $temas > 0) {
            return 'En ampliación';
        }

        return 'Base diagnóstica';
    }

    private function clasificarPlantilla(PlantillaEvaluacion $plantilla): string
    {
        $nombre = Str::lower($plantilla->nombre_plan);
        $codigos = $plantilla->preguntas
            ->pluck('tema.area.materia.codigo_mat')
            ->filter()
            ->unique()
            ->values();

        if (Str::contains($nombre, ['simulacro', 'sim-oficial'])) {
            return 'Final Preuniversitaria';
        }

        if (Str::contains($nombre, ['diagnóstico', 'diagnóstica', 'dia-01'])) {
            return 'Diagnóstica';
        }

        if ($codigos->count() > 1) {
            return 'Mixta';
        }

        return match ($codigos->first()) {
            'MAT' => 'Matemática',
            'FIS' => 'Física',
            'QMC' => 'Química',
            'PAA' => 'PAA / Razonamiento',
            default => 'Mixta',
        };
    }
}
