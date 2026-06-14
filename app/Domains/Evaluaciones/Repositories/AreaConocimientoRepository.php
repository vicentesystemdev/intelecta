<?php

namespace App\Domains\Evaluaciones\Repositories;

use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AreaConocimientoRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return AreaConocimiento::query()
            ->with('materia:id_mat,nombre_mat,codigo_mat')
            ->withCount('temas')
            ->when(
                $filters['id_mat'] ?? null,
                fn (Builder $query, int|string $value) => $query->where('id_mat', $value),
            )
            ->orderBy('nombre_area')
            ->paginate(12)
            ->withQueryString();
    }

    public function materias(): Collection
    {
        return Materia::query()
            ->orderBy('nombre_mat')
            ->get(['id_mat', 'nombre_mat', 'codigo_mat', 'estado_mat']);
    }

    public function create(AreaConocimientoData $data): AreaConocimiento
    {
        return AreaConocimiento::create($data->toArray());
    }

    public function update(AreaConocimiento $area, AreaConocimientoData $data): AreaConocimiento
    {
        $area->update($data->toArray());

        return $area->refresh();
    }
}
