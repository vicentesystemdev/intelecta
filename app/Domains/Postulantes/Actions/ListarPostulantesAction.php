<?php

namespace App\Domains\Postulantes\Actions;

use App\Domains\Postulantes\Services\PostulanteService;
use App\Models\User;

class ListarPostulantesAction
{
    public function __construct(
        private readonly PostulanteService $service,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters, User $user): array
    {
        return $this->service->list($filters, $user);
    }
}
