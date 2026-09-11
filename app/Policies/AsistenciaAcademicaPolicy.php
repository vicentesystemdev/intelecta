<?php

namespace App\Policies;

use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Models\User;

class AsistenciaAcademicaPolicy
{
    public function __construct(private readonly AmbitoDocenteService $ambito) {}

    public function view(User $user, AsistenciaAcademica $asistencia): bool
    {
        return $user->can('asistencia.ver')
            && $this->ambito->puedeVerAsistencia($user, $asistencia);
    }

    public function update(User $user, AsistenciaAcademica $asistencia): bool
    {
        return $user->can('asistencia.editar')
            && $this->ambito->puedeVerAsistencia($user, $asistencia, 'asistencia.editar');
    }
}
