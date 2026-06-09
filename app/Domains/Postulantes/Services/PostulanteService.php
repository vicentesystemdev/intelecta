<?php

namespace App\Domains\Postulantes\Services;

use App\Domains\Postulantes\DTOs\PostulanteData;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Repositories\PostulanteRepository;
use Illuminate\Support\Facades\DB;

class PostulanteService
{
    public function __construct(
        private readonly PostulanteRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function list(array $filters): array
    {
        return [
            'postulantes' => $this->repository->paginate($filters),
            'opciones' => $this->repository->formOptions(),
        ];
    }

    public function create(PostulanteData $data): Postulante
    {
        return DB::transaction(
            fn () => $this->repository->create($data)
        );
    }

    public function update(Postulante $postulante, PostulanteData $data): Postulante
    {
        return DB::transaction(
            fn () => $this->repository->update($postulante, $data)
        );
    }

    public function changeStatus(Postulante $postulante, string $estado): Postulante
    {
        return DB::transaction(
            fn () => $this->repository->changeStatus($postulante, $estado)
        );
    }

    public function find(int $id): Postulante
    {
        return $this->repository->find($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return $this->repository->formOptions();
    }
}
