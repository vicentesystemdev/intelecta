<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Repositories\AreaConocimientoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AreaConocimientoService
{
    public function __construct(private readonly AreaConocimientoRepository $repository) {}

    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function materias(): Collection
    {
        return $this->repository->materias();
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
