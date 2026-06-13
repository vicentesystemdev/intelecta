<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\CuotaAcademicaData;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Services\MatriculaCuotaService;

class GuardarCuotaAcademicaAction
{
    public function __construct(private readonly MatriculaCuotaService $service) {}

    public function execute(CuotaAcademicaData $data, ?CuotaAcademica $cuota = null): CuotaAcademica
    {
        return $this->service->saveCuota($data, $cuota);
    }
}
