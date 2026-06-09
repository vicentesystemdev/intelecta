<?php

namespace App\Domains\Evaluaciones\Repositories;

use App\Domains\Evaluaciones\DTOs\PlantillaEvaluacionData;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Materia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PlantillaEvaluacionRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return PlantillaEvaluacion::query()
            ->withCount('preguntas')
            ->withSum('preguntas as puntaje_total', 'plantilla_preguntas.puntaje_pp')
            ->when($filters['buscar'] ?? null, fn (Builder $query, string $search) => $query->whereRaw('LOWER(nombre_plan) LIKE ?', ['%'.mb_strtolower($search).'%']))
            ->when(
                $filters['id_mat'] ?? null,
                fn (Builder $query, int|string $id) => $query->whereHas(
                    'preguntas.tema.area',
                    fn (Builder $query) => $query->where('id_mat', $id)
                )
            )
            ->when($filters['estado_plan'] ?? null, fn (Builder $query, string $value) => $query->where('estado_plan', $value))
            ->latest('id_plan')
            ->paginate(12)
            ->withQueryString();
    }

    public function create(PlantillaEvaluacionData $data): PlantillaEvaluacion
    {
        $plantilla = PlantillaEvaluacion::create($data->templateAttributes());
        $this->syncQuestions($plantilla, $data->preguntas);

        return $plantilla->load('preguntas.tema.area.materia');
    }

    public function update(PlantillaEvaluacion $plantilla, PlantillaEvaluacionData $data): PlantillaEvaluacion
    {
        $plantilla->update($data->templateAttributes());
        $this->syncQuestions($plantilla, $data->preguntas);

        return $plantilla->refresh()->load('preguntas.tema.area.materia');
    }

    public function changeStatus(PlantillaEvaluacion $plantilla, string $estado): PlantillaEvaluacion
    {
        $plantilla->update(['estado_plan' => $estado]);

        return $plantilla->refresh();
    }

    public function find(int $id): PlantillaEvaluacion
    {
        return PlantillaEvaluacion::with(['preguntas.tema.area.materia', 'preguntas.alternativas'])->findOrFail($id);
    }

    public function questions(): Collection
    {
        return Pregunta::query()
            ->with([
                'tema:id_tem,id_area,nombre_tem',
                'tema.area:id_area,id_mat,nombre_area',
                'tema.area.materia:id_mat,codigo_mat,nombre_mat,color_mat',
            ])
            ->where('estado_preg', 'activo')
            ->orderBy('id_preg')
            ->get(['id_preg', 'id_tem', 'enunciado_preg', 'dificultad_preg']);
    }

    public function subjects(): Collection
    {
        return Materia::query()
            ->where('estado_mat', 'activo')
            ->orderBy('nombre_mat')
            ->get(['id_mat', 'codigo_mat', 'nombre_mat', 'color_mat']);
    }

    private function syncQuestions(PlantillaEvaluacion $plantilla, array $preguntas): void
    {
        $sync = [];

        foreach ($preguntas as $index => $pregunta) {
            $sync[(int) $pregunta['id_preg']] = [
                'orden_pp' => $pregunta['orden_pp'] ?? ($index + 1),
                'puntaje_pp' => $pregunta['puntaje_pp'],
            ];
        }

        $plantilla->preguntas()->sync($sync);
    }
}
