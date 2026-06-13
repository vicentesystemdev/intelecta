<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Services\TutorAcademicoService;

class GuardarTutorAcademicoAction
{
    public function __construct(private readonly TutorAcademicoService $service) {}

    public function execute(TutorAcademicoData $data, ?TutorAcademico $tutor = null): TutorAcademico
    {
        return $this->service->save($data, $tutor);
    }
}
