<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\MateriaData;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Repositories\MateriaRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MateriaService
{
    public function __construct(private readonly MateriaRepository $repository) {}

    public function index(): array
    {
        $items = $this->repository->allWithMetrics();

        return [
            'tipo' => 'materias',
            'items' => $items,
            'metricas' => [
                'total' => $items->count(),
                'activos' => $items->where('estado_mat', 'activo')->count(),
                'vinculados' => $items->where('areas_count', '>', 0)->count(),
                'postulantes' => $items->sum('temas_count'),
            ],
        ];
    }

    public function save(MateriaData $data, ?Materia $materia = null): Materia
    {
        try {
            return DB::transaction(function () use ($data, $materia): Materia {
                if (! $materia) {
                    return $this->repository->create($data);
                }

                $locked = Materia::query()->lockForUpdate()->findOrFail($materia->getKey());

                return $this->repository->update($locked, $data);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $field = str_contains(mb_strtolower($exception->getMessage()), 'nombre')
                ? 'nombre_mat'
                : 'codigo_mat';

            throw ValidationException::withMessages([
                $field => 'Otra operación ya registró una materia equivalente. Actualice el catálogo e intente nuevamente.',
            ]);
        }
    }

    public function changeStatus(Materia $materia, string $estado): Materia
    {
        return DB::transaction(function () use ($materia, $estado): Materia {
            $locked = Materia::query()->lockForUpdate()->findOrFail($materia->getKey());
            if ($estado === 'inactivo' && $locked->areas()->where('estado_area', 'activo')->exists()) {
                throw ValidationException::withMessages([
                    'estado_mat' => 'No se puede inactivar la materia mientras conserve áreas activas.',
                ]);
            }

            return $this->repository->changeStatus($locked, $estado);
        }, 3);
    }
}
