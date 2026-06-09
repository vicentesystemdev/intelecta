<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Services\AreaConocimientoService;

class CrearAreaConocimientoAction
{
    public function __construct(private readonly AreaConocimientoService $service) {}

    public function execute(AreaConocimientoData $data)
    {
        return $this->service->create($data);
    }
}
