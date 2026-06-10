<?php

namespace App\Domains\Academico\Actions;

use App\Domains\Academico\DTOs\GrupoAcademicoData;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Services\AcademicoService;

class GuardarGrupoAcademicoAction
{
    public function __construct(private readonly AcademicoService $service) {}

    public function execute(GrupoAcademicoData $data, ?GrupoAcademico $grupo = null): GrupoAcademico
    {
        return $this->service->saveGrupo($data, $grupo);
    }
}
