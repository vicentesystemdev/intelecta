<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Services\TutorAcademicoService;
use App\Models\User;

class GuardarTutorAcademicoAction
{
    public function __construct(private readonly TutorAcademicoService $service) {}

    public function execute(TutorAcademicoData $data, User $actor, ?TutorAcademico $tutor = null): TutorAcademico
    {
        return $this->service->save($data, $actor, $tutor);
    }
}
