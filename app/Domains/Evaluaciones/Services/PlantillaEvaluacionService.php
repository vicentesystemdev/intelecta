<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\PlantillaEvaluacionData;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Repositories\PlantillaEvaluacionRepository;
use Illuminate\Support\Facades\DB;

class PlantillaEvaluacionService
{
    public function __construct(
        private readonly PlantillaEvaluacionRepository $repository,
        private readonly ConsistenciaEvaluacionService $consistencia,
    ) {}

    public function list(array $filters): array
    {
        return ['plantillas' => $this->repository->paginate($filters)];
    }

    public function create(PlantillaEvaluacionData $data): PlantillaEvaluacion
    {
        return DB::transaction(function () use ($data): PlantillaEvaluacion {
            $ids = $this->preguntaIds($data);
            if ($data->estadoPlan === 'activa') {
                $this->consistencia->validarPreguntasAplicables($ids);
            } else {
                $this->consistencia->validarPreguntasNuevas($ids);
            }

            return $this->repository->create($data);
        });
    }

    public function update(PlantillaEvaluacion $plantilla, PlantillaEvaluacionData $data): PlantillaEvaluacion
    {
        return DB::transaction(function () use ($plantilla, $data): PlantillaEvaluacion {
            $plantilla = PlantillaEvaluacion::query()->lockForUpdate()->findOrFail($plantilla->getKey());
            $ids = $this->preguntaIds($data);
            $actuales = $this->estructuraActual($plantilla);
            $nueva = $this->estructuraNueva($data);
            $cambiaEstructura = $actuales !== $nueva;
            $seActiva = $plantilla->estado_plan !== 'activa' && $data->estadoPlan === 'activa';

            if ($data->estadoPlan === 'activa' && ($seActiva || $cambiaEstructura)) {
                $this->consistencia->validarPreguntasAplicables($ids);
            } elseif ($data->estadoPlan !== 'activa') {
                $this->consistencia->validarPreguntasNuevas(array_values(array_diff($ids, array_keys($actuales))));
            }

            return $this->repository->update($plantilla, $data);
        });
    }

    public function changeStatus(PlantillaEvaluacion $plantilla): PlantillaEvaluacion
    {
        return DB::transaction(function () use ($plantilla): PlantillaEvaluacion {
            $plantilla = PlantillaEvaluacion::query()->lockForUpdate()->findOrFail($plantilla->getKey());
            $estado = $plantilla->estado_plan === 'activa' ? 'inactiva' : 'activa';

            if ($estado === 'activa') {
                $this->consistencia->validarPreguntasAplicables(
                    $plantilla->preguntas()->withTrashed()->pluck('preguntas.id_preg')->all(),
                );
            }

            return $this->repository->changeStatus($plantilla, $estado);
        }, 3);
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

    private function preguntaIds(PlantillaEvaluacionData $data): array
    {
        return array_map(
            fn (array $pregunta): int => (int) $pregunta['id_preg'],
            $data->preguntas,
        );
    }

    private function estructuraActual(PlantillaEvaluacion $plantilla): array
    {
        return $plantilla->preguntas()
            ->withTrashed()
            ->get()
            ->mapWithKeys(fn ($pregunta) => [(int) $pregunta->id_preg => [
                'orden' => (int) $pregunta->pivot->orden_pp,
                'puntaje' => round((float) $pregunta->pivot->puntaje_pp, 2),
            ]])
            ->sortKeys()
            ->all();
    }

    private function estructuraNueva(PlantillaEvaluacionData $data): array
    {
        return collect($data->preguntas)
            ->mapWithKeys(fn (array $pregunta, int $index) => [(int) $pregunta['id_preg'] => [
                'orden' => (int) ($pregunta['orden_pp'] ?? ($index + 1)),
                'puntaje' => round((float) $pregunta['puntaje_pp'], 2),
            ]])
            ->sortKeys()
            ->all();
    }
}
