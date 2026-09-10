<?php

namespace App\Console\Commands;

use App\Domains\Academico\Services\MigrarIdentidadTutorService;
use Illuminate\Console\Command;

class MigrarIdentidadTutor extends Command
{
    protected $signature = 'tutores:migrar-identidad {--dry-run : Solo comprobar, sin escribir} {--apply : Aplicar después de un respaldo verificado}';

    protected $description = 'Migra la identidad explícita Tutor → Personal, incluidos archivados; por defecto solo dry-run.';

    public function handle(MigrarIdentidadTutorService $service): int
    {
        $report = $service->execute(! $this->option('apply') || $this->option('dry-run'));
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $report['conflictos'] ? self::FAILURE : self::SUCCESS;
    }
}
