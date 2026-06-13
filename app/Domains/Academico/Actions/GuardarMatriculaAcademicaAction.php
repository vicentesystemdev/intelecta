<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\MatriculaAcademicaData;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Services\MatriculaCuotaService;

class GuardarMatriculaAcademicaAction
{
    public function __construct(private readonly MatriculaCuotaService $service) {}

    public function execute(MatriculaAcademicaData $data, ?MatriculaAcademica $matricula = null): MatriculaAcademica
    {
        return $this->service->saveMatricula($data, $matricula);
    }
}
