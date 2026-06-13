<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Repositories\TutorAcademicoRepository;
use Illuminate\Support\Facades\DB;

class TutorAcademicoService
{
    public function __construct(private readonly TutorAcademicoRepository $repository) {}

    public function index(array $filters): array
    {
        return [
            'tutores' => $this->repository->paginate($filters),
            'especialidades' => $this->repository->especialidades(),
            'usuarios' => $this->repository->usuariosOptions(),
        ];
    }

    public function show(TutorAcademico $tutor): TutorAcademico
    {
        return $this->repository->findForDetail($tutor);
    }

    public function save(TutorAcademicoData $data, ?TutorAcademico $tutor = null): TutorAcademico
    {
        return DB::transaction(fn () => $tutor
            ? $this->repository->update($tutor, $data)
            : $this->repository->create($data));
    }
}
