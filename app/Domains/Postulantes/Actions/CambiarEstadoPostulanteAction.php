<?php

namespace App\Domains\Postulantes\Actions;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\PostulanteService;

class CambiarEstadoPostulanteAction
{
    public function __construct(
        private readonly PostulanteService $service,
    ) {}

    public function execute(Postulante $postulante): Postulante
    {
        $nuevoEstado = $postulante->estado_post === 'activo' ? 'inactivo' : 'activo';

        return $this->service->changeStatus($postulante, $nuevoEstado);
    }
}
