<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Services\AsistenciaAcademicaService;
use App\Models\User;
use Illuminate\Support\Collection;

class GuardarAsistenciaAcademicaAction
{
    public function __construct(private readonly AsistenciaAcademicaService $service) {}

    public function execute(
        AsistenciaAcademicaData $data,
        User $user,
        ?AsistenciaAcademica $asistencia = null,
    ): AsistenciaAcademica {
        return $this->service->save($data, $user, $asistencia);
    }

    public function executeGroup(array $data, User $user): Collection
    {
        return $this->service->saveGroup($data, $user);
    }
}
