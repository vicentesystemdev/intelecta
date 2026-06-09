<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\Services\TemaService;

class ListarTemasAction
{
    public function __construct(private readonly TemaService $service) {}

    public function execute(array $filters)
    {
        return $this->service->list($filters);
    }
}
