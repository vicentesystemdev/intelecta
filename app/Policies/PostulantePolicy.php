<?php

namespace App\Policies;

use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;

class PostulantePolicy
{
    public function __construct(private readonly AmbitoDocenteService $ambito) {}

    public function view(User $user, Postulante $postulante): bool
    {
        return $user->can('postulantes.ver')
            && $this->ambito->puedeVerPostulante($user, $postulante);
    }

    public function viewAcademicFile(User $user, Postulante $postulante): bool
    {
        return $user->can('ficha-academica.ver')
            && $this->ambito->puedeVerPostulante($user, $postulante, 'ficha-academica.ver');
    }
}
