<?php

namespace App\Policies;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Models\User;

class GrupoAcademicoPolicy
{
    public function __construct(private readonly AmbitoDocenteService $ambito) {}

    public function view(User $user, GrupoAcademico $grupo): bool
    {
        return $user->can('grupos.ver') && $this->ambito->puedeVerGrupo($user, $grupo);
    }
}
