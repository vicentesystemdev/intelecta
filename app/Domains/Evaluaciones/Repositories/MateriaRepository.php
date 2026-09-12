<?php

namespace App\Domains\Evaluaciones\Repositories;

use App\Domains\Evaluaciones\DTOs\MateriaData;
use App\Domains\Evaluaciones\Models\Materia;
use Illuminate\Support\Collection;

class MateriaRepository
{
    public function allWithMetrics(): Collection
    {
        return Materia::query()
            ->with(['areas' => fn ($query) => $query->withCount('temas')])
            ->withCount('areas')
            ->orderBy('nombre_mat')
            ->get()
            ->each(fn (Materia $materia) => $materia->setAttribute(
                'temas_count',
                $materia->areas->sum('temas_count'),
            ));
    }

    public function create(MateriaData $data): Materia
    {
        return Materia::create([...$data->toArray(), 'estado_mat' => 'activo']);
    }

    public function update(Materia $materia, MateriaData $data): Materia
    {
        $materia->update($data->toArray());

        return $materia->refresh();
    }

    public function changeStatus(Materia $materia, string $estado): Materia
    {
        $materia->update(['estado_mat' => $estado]);

        return $materia->refresh();
    }
}
