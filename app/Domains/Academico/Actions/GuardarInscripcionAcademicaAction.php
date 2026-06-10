<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\InscripcionAcademicaData;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Services\AcademicoService;

class GuardarInscripcionAcademicaAction
{
    public function __construct(private readonly AcademicoService $service) {}

    public function execute(InscripcionAcademicaData $data, ?InscripcionAcademica $inscripcion = null): InscripcionAcademica
    {
        return $this->service->saveInscripcion($data, $inscripcion);
    }
}
