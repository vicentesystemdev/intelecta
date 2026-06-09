<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\TemaData;
use App\Domains\Evaluaciones\Services\TemaService;

class CrearTemaAction
{
    public function __construct(private readonly TemaService $service) {}

    public function execute(TemaData $data)
    {
        return $this->service->create($data);
    }
}
