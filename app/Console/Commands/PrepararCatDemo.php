<?php

namespace App\Console\Commands;

use App\Domains\Cat\Services\PrepararInstrumentoDemo;
use Illuminate\Console\Command;

class PrepararCatDemo extends Command
{
    protected $signature = 'intelecta:preparar-cat-demo';

    protected $description = 'Crea solo el instrumento CAT DEMO y sus asociaciones 1–16; no modifica preguntas ni plantillas';

    public function handle(PrepararInstrumentoDemo $preparar): int
    {
        $instrumento = $preparar->ejecutar();
        $this->info('Instrumento CAT DEMO disponible: '.$instrumento->id_instrumento_cat.' (parámetros simulados, no calibrados).');

        return self::SUCCESS;
    }
}
