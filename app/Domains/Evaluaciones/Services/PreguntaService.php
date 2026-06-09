<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Repositories\PreguntaRepository;
use Illuminate\Support\Facades\DB;

class PreguntaService
{
    public function __construct(private readonly PreguntaRepository $repository) {}

    public function list(array $filters): array
    {
        return ['preguntas' => $this->repository->paginate($filters), 'opciones' => $this->repository->options()];
    }

    public function create(PreguntaData $data): Pregunta
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    public function update(Pregunta $pregunta, PreguntaData $data): Pregunta
    {
        return DB::transaction(fn () => $this->repository->update($pregunta, $data));
    }

    public function changeStatus(Pregunta $pregunta): Pregunta
    {
        $estado = $pregunta->estado_preg === 'activo' ? 'inactivo' : 'activo';

        return $this->repository->changeStatus($pregunta, $estado);
    }

    public function find(int $id): Pregunta
    {
        return $this->repository->find($id);
    }

    public function options(): array
    {
        return $this->repository->options();
    }
}
