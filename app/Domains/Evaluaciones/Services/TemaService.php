<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\TemaData;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Evaluaciones\Repositories\TemaRepository;

class TemaService
{
    public function __construct(private readonly TemaRepository $repository) {}

    public function list(array $filters): array
    {
        return ['temas' => $this->repository->paginate($filters), 'areas' => $this->repository->areas()];
    }

    public function create(TemaData $data): Tema
    {
        return $this->repository->create($data);
    }

    public function update(Tema $tema, TemaData $data): Tema
    {
        return $this->repository->update($tema, $data);
    }

    public function areas()
    {
        return $this->repository->areas();
    }
}
