<?php

namespace App\Domains\Evaluaciones\Repositories;

use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PreguntaRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Pregunta::query()
            ->with(['tema:id_tem,id_area,nombre_tem', 'tema.area:id_area,nombre_area'])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $query->whereRaw('LOWER(enunciado_preg) LIKE ?', ['%'.mb_strtolower($search).'%']);
            })
            ->when(
                $filters['id_area'] ?? null,
                fn (Builder $query, int|string $id) => $query->whereHas(
                    'tema',
                    fn (Builder $query) => $query->where('id_area', $id)
                )
            )
            ->when(
                $filters['id_tem'] ?? null,
                fn (Builder $query, int|string $id) => $query->where('id_tem', $id)
            )
            ->when($filters['dificultad_preg'] ?? null, fn (Builder $query, string $value) => $query->where('dificultad_preg', $value))
            ->when($filters['tipo_preg'] ?? null, fn (Builder $query, string $value) => $query->where('tipo_preg', $value))
            ->when($filters['estado_preg'] ?? null, fn (Builder $query, string $value) => $query->where('estado_preg', $value))
            ->latest('id_preg')
            ->paginate(12)
            ->withQueryString();
    }

    public function create(PreguntaData $data): Pregunta
    {
        $pregunta = Pregunta::create($data->questionAttributes());
        $this->replaceAlternatives($pregunta, $data->alternativas);

        return $pregunta->load(['tema.area', 'alternativas']);
    }

    public function update(Pregunta $pregunta, PreguntaData $data): Pregunta
    {
        $pregunta->update($data->questionAttributes());
        $this->replaceAlternatives($pregunta, $data->alternativas);

        return $pregunta->refresh()->load(['tema.area', 'alternativas']);
    }

    public function changeStatus(Pregunta $pregunta, string $estado): Pregunta
    {
        $pregunta->update(['estado_preg' => $estado]);

        return $pregunta->refresh();
    }

    public function find(int $id): Pregunta
    {
        return Pregunta::with(['tema.area', 'alternativas', 'plantillas'])->findOrFail($id);
    }

    public function options(): array
    {
        return [
            'areas' => AreaConocimiento::query()->where('estado_area', 'activo')->orderBy('nombre_area')->get(['id_area', 'nombre_area']),
            'temas' => Tema::query()->with('area:id_area,nombre_area')->where('estado_tem', 'activo')->orderBy('nombre_tem')->get(['id_tem', 'id_area', 'nombre_tem']),
        ];
    }

    private function replaceAlternatives(Pregunta $pregunta, array $alternativas): void
    {
        $pregunta->alternativas()->delete();

        foreach ($alternativas as $index => $alternativa) {
            $pregunta->alternativas()->create([
                'texto_alt' => $alternativa['texto_alt'],
                'letra_alt' => $alternativa['letra_alt'] ?? chr(65 + $index),
                'es_correcta_alt' => (bool) ($alternativa['es_correcta_alt'] ?? false),
                'orden_alt' => $alternativa['orden_alt'] ?? ($index + 1),
                'estado_alt' => $alternativa['estado_alt'] ?? 'activo',
            ]);
        }
    }
}
