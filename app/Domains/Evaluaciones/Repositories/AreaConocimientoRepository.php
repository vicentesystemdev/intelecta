<?php

namespace App\Domains\Evaluaciones\Repositories;

use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AreaConocimientoRepository
{
    public function paginate(): LengthAwarePaginator
    {
        return AreaConocimiento::query()
            ->withCount('temas')
            ->orderBy('nombre_area')
            ->paginate(12);
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
