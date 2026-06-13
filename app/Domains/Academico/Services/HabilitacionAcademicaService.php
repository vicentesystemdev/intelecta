<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\HabilitacionAcademicaData;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Repositories\AcademicoRepository;
use App\Domains\Academico\Repositories\HabilitacionAcademicaRepository;
use Illuminate\Support\Facades\DB;

class HabilitacionAcademicaService
{
    public function __construct(
        private readonly HabilitacionAcademicaRepository $repository,
        private readonly AcademicoRepository $academico,
    ) {}

    public function index(array $filters): array
    {
        return [
            'habilitaciones' => $this->repository->paginate($filters),
            'metricas' => $this->repository->metrics(),
            'programas' => $this->academico->programasOptions(),
            'grupos' => $this->academico->gruposOptions(),
        ];
    }

    public function update(
        HabilitacionAcademica $habilitacion,
        HabilitacionAcademicaData $data,
    ): HabilitacionAcademica {
        return DB::transaction(fn () => $this->repository->update($habilitacion, $data));
    }
}
