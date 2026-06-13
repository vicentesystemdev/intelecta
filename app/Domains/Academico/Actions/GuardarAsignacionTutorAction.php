<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\AsignacionTutorData;
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Services\AsignacionTutorService;

class GuardarAsignacionTutorAction
{
    public function __construct(private readonly AsignacionTutorService $service) {}

    public function execute(AsignacionTutorData $data, ?AsignacionTutor $asignacion = null): AsignacionTutor
    {
        return $this->service->save($data, $asignacion);
    }
}
