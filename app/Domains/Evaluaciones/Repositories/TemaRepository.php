<?php

namespace App\Domains\Evaluaciones\Repositories;

use App\Domains\Evaluaciones\DTOs\TemaData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TemaRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Tema::query()
            ->with('area.materia:id_mat,nombre_mat,codigo_mat')
            ->withCount('preguntas')
            ->when(
                $filters['id_area'] ?? null,
                fn (Builder $query, int|string $id) => $query->where('id_area', $id)
            )
            ->orderBy('nombre_tem')
            ->paginate(12)
            ->withQueryString();
    }

    public function create(TemaData $data): Tema
    {
        return Tema::create($data->toArray());
    }

    public function update(Tema $tema, TemaData $data): Tema
    {
        $tema->update($data->toArray());

        return $tema->refresh();
    }

    public function areas(): Collection
    {
        return AreaConocimiento::query()
            ->with('materia:id_mat,nombre_mat,codigo_mat')
            ->where('estado_area', 'activo')
            ->orderBy('nombre_area')
            ->get(['id_area', 'id_mat', 'nombre_area']);
    }
}
