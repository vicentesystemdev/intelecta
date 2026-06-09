<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\AreaConocimientoData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Services\AreaConocimientoService;

class ActualizarAreaConocimientoAction
{
    public function __construct(private readonly AreaConocimientoService $service) {}

    public function execute(AreaConocimiento $area, AreaConocimientoData $data)
    {
        return $this->service->update($area, $data);
    }
}
