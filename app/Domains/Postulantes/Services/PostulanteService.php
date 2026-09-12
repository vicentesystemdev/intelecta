<?php

namespace App\Domains\Postulantes\Services;

use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Postulantes\DTOs\PostulanteData;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Repositories\PostulanteRepository;
use App\Models\User;
use App\Support\Validation\InputNormalizer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostulanteService
{
    public function __construct(
        private readonly PostulanteRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function list(array $filters, User $user): array
    {
        return [
            'postulantes' => $this->repository->paginate($filters, $user),
            'opciones' => $this->repository->formOptions($user),
        ];
    }

    public function create(PostulanteData $data): Postulante
    {
        try {
            return DB::transaction(function () use ($data): Postulante {
                return $this->repository->create($this->resolveCatalogs($data));
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'catalogos' => 'El colegio, universidad, carrera o postulante ya fue registrado por otra operación. Actualice las opciones e intente nuevamente.',
            ]);
        }
    }

    public function update(Postulante $postulante, PostulanteData $data): Postulante
    {
        try {
            return DB::transaction(function () use ($postulante, $data): Postulante {
                $locked = Postulante::query()->lockForUpdate()->findOrFail($postulante->getKey());

                return $this->repository->update($locked, $this->resolveCatalogs($data));
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'catalogos' => 'El colegio, universidad, carrera o postulante ya fue registrado por otra operación. Actualice las opciones e intente nuevamente.',
            ]);
        }
    }

    public function changeStatus(Postulante $postulante, string $estado): Postulante
    {
        return DB::transaction(
            fn () => $this->repository->changeStatus($postulante, $estado)
        );
    }

    public function find(int $id, User $user): Postulante
    {
        return $this->repository->find($id, $user);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return $this->repository->formOptions();
    }

    private function resolveCatalogs(PostulanteData $data): PostulanteData
    {
        $universidadIndicada = $data->crearOtraUniversidad || $data->idUni !== null;
        $carreraCompatibleIndicada = $data->crearOtraCarrera
            || ($data->idCar !== null && ! $data->crearOtraUniversidad);
        if ($universidadIndicada && ! $carreraCompatibleIndicada) {
            throw ValidationException::withMessages([
                'id_car' => 'Seleccione o registre una carrera para la universidad postulada.',
            ]);
        }

        $colegioId = $data->idCol;
        if ($data->crearOtroColegio) {
            $colegio = Colegio::query()->get()->first(
                fn (Colegio $item) => InputNormalizer::key($item->nombre_col) === InputNormalizer::key($data->otroColegioNombre),
            );
            if ($colegio && $colegio->estado_col !== 'activo') {
                throw ValidationException::withMessages([
                    'otro_colegio_nombre' => 'Ese colegio ya existe, pero está inactivo. Solicite su revisión administrativa.',
                ]);
            }
            $colegio ??= Colegio::create([
                'nombre_col' => $data->otroColegioNombre,
                'estado_col' => 'activo',
            ]);
            $colegioId = $colegio->id_col;
        }

        $universidadId = $data->idUni;
        if ($data->crearOtraUniversidad) {
            $universidad = Universidad::query()->get()->first(
                fn (Universidad $item) => InputNormalizer::key($item->nombre_uni) === InputNormalizer::key($data->otraUniversidadNombre),
            );
            if ($universidad && $universidad->estado_uni !== 'activo') {
                throw ValidationException::withMessages([
                    'otra_universidad_nombre' => 'Esa universidad ya existe, pero está inactiva. Solicite su revisión administrativa.',
                ]);
            }
            $universidad ??= Universidad::create([
                'nombre_uni' => $data->otraUniversidadNombre,
                'sigla_uni' => $data->otraUniversidadSigla,
                'estado_uni' => 'activo',
            ]);
            $universidadId = $universidad->id_uni;
        }

        $carreraId = $data->idCar;
        if ($data->crearOtraCarrera) {
            $carrera = Carrera::query()
                ->where('id_uni', $universidadId)
                ->get()
                ->first(fn (Carrera $item) => InputNormalizer::key($item->nombre_car) === InputNormalizer::key($data->otraCarreraNombre));
            if ($carrera && $carrera->estado_car !== 'activo') {
                throw ValidationException::withMessages([
                    'otra_carrera_nombre' => 'Esa carrera ya existe para la universidad, pero está inactiva. Solicite su revisión administrativa.',
                ]);
            }
            $carrera ??= Carrera::create([
                'id_uni' => $universidadId,
                'nombre_car' => $data->otraCarreraNombre,
                'estado_car' => 'activo',
            ]);
            $carreraId = $carrera->id_car;
        }

        return $data->withCatalogs($colegioId, $carreraId);
    }
}
