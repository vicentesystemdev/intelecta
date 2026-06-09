<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\PlantillaEvaluacionData;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Repositories\PlantillaEvaluacionRepository;
use Illuminate\Support\Facades\DB;

class PlantillaEvaluacionService
{
    public function __construct(private readonly PlantillaEvaluacionRepository $repository) {}

    public function list(array $filters): array
    {
        return ['plantillas' => $this->repository->paginate($filters)];
    }

    public function create(PlantillaEvaluacionData $data): PlantillaEvaluacion
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    public function update(PlantillaEvaluacion $plantilla, PlantillaEvaluacionData $data): PlantillaEvaluacion
    {
        return DB::transaction(fn () => $this->repository->update($plantilla, $data));
    }

    public function changeStatus(PlantillaEvaluacion $plantilla): PlantillaEvaluacion
    {
        $estado = $plantilla->estado_plan === 'activa' ? 'inactiva' : 'activa';

        return $this->repository->changeStatus($plantilla, $estado);
    }

    public function find(int $id): PlantillaEvaluacion
    {
        return $this->repository->find($id);
    }

    public function questions()
    {
        return $this->repository->questions();
    }

    public function subjects()
    {
        return $this->repository->subjects();
    }
}
