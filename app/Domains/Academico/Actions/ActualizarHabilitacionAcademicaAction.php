<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\HabilitacionAcademicaData;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Services\HabilitacionAcademicaService;

class ActualizarHabilitacionAcademicaAction
{
    public function __construct(private readonly HabilitacionAcademicaService $service) {}

    public function execute(HabilitacionAcademica $habilitacion, HabilitacionAcademicaData $data): HabilitacionAcademica
    {
        return $this->service->update($habilitacion, $data);
    }
}
