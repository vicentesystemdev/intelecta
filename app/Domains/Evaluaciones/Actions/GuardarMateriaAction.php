<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\MateriaData;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Services\MateriaService;

class GuardarMateriaAction
{
    public function __construct(private readonly MateriaService $service) {}

    public function execute(MateriaData $data, ?Materia $materia = null): Materia
    {
        return $this->service->save($data, $materia);
    }
}
