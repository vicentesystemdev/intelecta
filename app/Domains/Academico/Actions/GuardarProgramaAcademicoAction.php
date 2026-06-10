<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\ProgramaAcademicoData;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Services\AcademicoService;

class GuardarProgramaAcademicoAction
{
    public function __construct(private readonly AcademicoService $service) {}

    public function execute(ProgramaAcademicoData $data, ?ProgramaAcademico $programa = null): ProgramaAcademico
    {
        return $this->service->savePrograma($data, $programa);
    }
}
