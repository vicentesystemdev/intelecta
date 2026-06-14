<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\Services\AreaConocimientoService;

class ListarAreasConocimientoAction
{
    public function __construct(private readonly AreaConocimientoService $service) {}

    public function execute(array $filters)
    {
        return $this->service->list($filters);
    }
}
