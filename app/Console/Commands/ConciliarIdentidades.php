<?php

namespace App\Console\Commands;

use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ConciliarIdentidades extends Command
{
    protected $signature = 'intelecta:conciliar-identidades {manifiesto : JSON de pares explícitos revisados}
        {--aplicar : Escribir; sin esta opción siempre es dry-run}
        {--actor= : SA responsable expresamente autorizado}
        {--motivo= : Referencia trazable de autorización}
        {--respaldo= : JSON del respaldo restaurado y verificado}';

    protected $description = 'Conciliación sin inferencias; dry-run obligatorio, confirmación, respaldo y rollback de lote';

    public function handle(ConciliacionIdentidadService $service): int
    {
        config(['cache.default' => 'array']);
        try {
            $path = (string) $this->argument('manifiesto');
            $manifest = $this->read($path);
            $sha = hash_file('sha256', $path);
            $receipt = $path.'.dry-run.json';
            if (! $this->option('aplicar')) {
                DB::beginTransaction();
                try {
                    if (DB::getDriverName() === 'pgsql') {
                        DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
                    }
                    $rows = $service->ejecutar($manifest);
                } finally {
                    DB::rollBack();
                }
                if (file_put_contents($receipt, json_encode(['sha256' => $sha, 'destino' => $service->destino(), 'fecha' => time()], JSON_THROW_ON_ERROR), LOCK_EX) === false) {
                    throw new RuntimeException('No se pudo guardar el comprobante privado de dry-run.');
                }
                @chmod($receipt, 0600);
                $this->info('DRY-RUN: cero escrituras en BD. SHA256 '.$sha);
            } else {
                $dryRun = $this->read($receipt);
                if (($dryRun['sha256'] ?? null) !== $sha || ($dryRun['destino'] ?? null) !== $service->destino()
                    || ($dryRun['fecha'] ?? 0) < time() - 86400) {
                    throw new RuntimeException('Se requiere dry-run del mismo manifiesto y conexión en las últimas 24 horas.');
                }
                $backup = $this->read((string) $this->option('respaldo'));
                if (($backup['destino'] ?? null) !== $service->destino() || ($backup['restaurado_verificado'] ?? false) !== true
                    || ! is_file($backup['archivo'] ?? '') || ! hash_equals($backup['sha256'] ?? '', hash_file('sha256', $backup['archivo']))) {
                    throw new RuntimeException('Respaldo inexistente, cambiado o sin restauración verificada para esta conexión.');
                }
                $actorId = filter_var($this->option('actor'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $actor = $actorId ? User::find($actorId) : null;
                if (! $actor?->canChangeLoginEmail()) {
                    throw new RuntimeException('Indica el SA activo verificado autorizado; no hay actor por defecto.');
                }
                if (! $this->confirm('¿Autorizas TODAS las filas de '.$sha.' en '.json_encode($service->destino()).' atribuidas al actor '.$actor->id.'?', false)) {
                    $this->warn('Cancelado sin cambios.');

                    return self::FAILURE;
                }
                $rows = $service->ejecutar($manifest, true, $actor, (string) $this->option('motivo'));
            }
            $this->table(['user_id', 'id_post', 'Resultado'], array_map('array_values', $rows));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function read(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Falta el manifiesto, dry-run o respaldo verificable.');
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }
}
