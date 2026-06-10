<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\SimulacroProgramadoData;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Services\AcademicoService;

class GuardarSimulacroProgramadoAction
{
    public function __construct(private readonly AcademicoService $service) {}

    public function execute(SimulacroProgramadoData $data, ?SimulacroProgramado $simulacro = null): SimulacroProgramado
    {
        return $this->service->saveSimulacro($data, $simulacro);
    }
}
