<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Repositories\AreaConocimientoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AreaConocimientoService
{
    public function __construct(private readonly AreaConocimientoRepository $repository) {}

    public function list(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    public function create(AreaConocimientoData $data): AreaConocimiento
    {
        return $this->repository->create($data);
    }

    public function update(AreaConocimiento $area, AreaConocimientoData $data): AreaConocimiento
    {
        return $this->repository->update($area, $data);
    }
}
