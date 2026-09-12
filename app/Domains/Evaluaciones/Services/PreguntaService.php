<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Repositories\PreguntaRepository;
use Illuminate\Support\Facades\DB;

class PreguntaService
{
    public function __construct(
        private readonly PreguntaRepository $repository,
        private readonly ConsistenciaEvaluacionService $consistencia,
    ) {}

    public function list(array $filters): array
    {
        return ['preguntas' => $this->repository->paginate($filters), 'opciones' => $this->repository->options()];
    }

    public function create(PreguntaData $data): Pregunta
    {
        return DB::transaction(function () use ($data): Pregunta {
            $this->consistencia->validarTaxonomia($data);
            $this->consistencia->validarAlternativas($data);

            return $this->repository->create($data);
        });
    }

    public function update(Pregunta $pregunta, PreguntaData $data): Pregunta
    {
        return DB::transaction(function () use ($pregunta, $data): Pregunta {
            $pregunta = Pregunta::query()->lockForUpdate()->findOrFail($pregunta->getKey());
            $this->consistencia->validarTaxonomia($data);
            $this->consistencia->validarAlternativas($data);

            return $this->repository->update($pregunta, $data);
        });
    }

    public function changeStatus(Pregunta $pregunta): Pregunta
    {
        return DB::transaction(function () use ($pregunta): Pregunta {
            $pregunta = Pregunta::query()->lockForUpdate()->findOrFail($pregunta->getKey());
            $estado = $pregunta->estado_preg === 'activo' ? 'inactivo' : 'activo';

            if ($estado === 'activo') {
                $this->consistencia->validarPreguntaAplicable(
                    $pregunta->setAttribute('estado_preg', 'activo'),
                );
            }

            return $this->repository->changeStatus($pregunta, $estado);
        }, 3);
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
