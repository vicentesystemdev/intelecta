<?php

namespace App\Domains\Postulantes\Actions;

use App\Domains\Postulantes\DTOs\PostulanteData;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\PostulanteService;

class CrearPostulanteAction
{
    public function __construct(
        private readonly PostulanteService $service,
    ) {}

    public function execute(PostulanteData $data): Postulante
    {
        return $this->service->create($data);
    }
}
