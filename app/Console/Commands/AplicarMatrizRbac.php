<?php

namespace App\Console\Commands;

use App\Domains\Seguridad\Services\DesplegarMatrizRbac;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class AplicarMatrizRbac extends Command
{
    protected $signature = 'rbac:matriz {--apply : Aplicar matriz Bloque 5} {--snapshot= : Archivo NUEVO obligatorio al aplicar} {--restore= : Restaurar un snapshot de esta misma base} {--confirm-database= : Nombre exacto de la BD para autorizar escrituras}';

    protected $description = 'Preflight por defecto; despliegue RBAC reversible sin seeders ni cambios de cuentas';

    public function handle(DesplegarMatrizRbac $service): int
    {
        try {
            if (! $this->option('apply') && ! $this->option('restore')) {
                $this->line(json_encode(['actual' => $service->matrix($service->state()), 'objetivo' => $service->target()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

                return self::SUCCESS;
            }
            if (($this->option('apply') && $this->option('restore')) || $this->option('confirm-database') !== DB::connection()->getDatabaseName()) {
                $this->error('Elige aplicar o restaurar y confirma el nombre exacto de la base.');

                return self::FAILURE;
            }
            if ($this->option('apply') && ! $this->option('snapshot')) {
                $this->error('Falta --snapshot (archivo nuevo, nunca sobrescrito).');

                return self::FAILURE;
            }
            $changed = $this->option('restore') ? $service->restore($this->option('restore')) : $service->apply($this->option('snapshot'));
            $this->info($changed ? 'Matriz actualizada; cache invalidada. Cuentas, roles de usuarios, perfiles y sesiones preservados.' : 'Sin cambios: estado ya alcanzado; cache invalidada.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
