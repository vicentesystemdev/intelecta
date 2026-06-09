<?php

namespace App\Domains\Evaluaciones\Actions;

use App\Domains\Evaluaciones\DTOs\TemaData;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Evaluaciones\Services\TemaService;

class ActualizarTemaAction
{
    public function __construct(private readonly TemaService $service) {}

    public function execute(Tema $tema, TemaData $data)
    {
        return $this->service->update($tema, $data);
    }
}
