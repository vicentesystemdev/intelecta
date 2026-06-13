<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\AsignacionTutorData;
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Repositories\AsignacionTutorRepository;
use App\Domains\Academico\Repositories\TutorAcademicoRepository;
use Illuminate\Support\Facades\DB;

class AsignacionTutorService
{
    public function __construct(
        private readonly AsignacionTutorRepository $repository,
        private readonly TutorAcademicoRepository $tutores,
    ) {}

    public function index(array $filters): array
    {
        return [
            'asignaciones' => $this->repository->paginate($filters),
            'tutores' => $this->tutores->options(),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
        ];
    }

    public function save(AsignacionTutorData $data, ?AsignacionTutor $asignacion = null): AsignacionTutor
    {
        return DB::transaction(fn () => $asignacion
            ? $this->repository->update($asignacion, $data)
            : $this->repository->create($data));
    }
}
