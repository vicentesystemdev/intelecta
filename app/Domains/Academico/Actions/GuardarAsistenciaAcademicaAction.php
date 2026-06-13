<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Services\AsistenciaAcademicaService;
use Illuminate\Support\Collection;

class GuardarAsistenciaAcademicaAction
{
    public function __construct(private readonly AsistenciaAcademicaService $service) {}

    public function execute(
        AsistenciaAcademicaData $data,
        ?AsistenciaAcademica $asistencia = null,
    ): AsistenciaAcademica {
        return $this->service->save($data, $asistencia);
    }

    public function executeGroup(array $data): Collection
    {
        return $this->service->saveGroup($data);
    }
}
