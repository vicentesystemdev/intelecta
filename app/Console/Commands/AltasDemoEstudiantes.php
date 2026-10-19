<?php

namespace App\Console\Commands;

use App\Domains\Postulantes\Services\AltasDemoEstudianteService;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class AltasDemoEstudiantes extends Command
{
    protected $signature = 'intelecta:altas-demo-estudiantes
        {manifiesto : JSON nuevo a preparar o manifiesto revisado existente}
        {--preparar : Generar propuesta pendiente sin escribir en BD}
        {--aplicar : Ejecutar exclusivamente las filas aprobadas; por defecto dry-run}
        {--actor= : SA explícitamente autorizado, ejecutado en ese contexto CLI}
        {--motivo= : Referencia de autorización de estas altas}
        {--respaldo= : Certificado JSON de restauración verificada}
        {--resumen= : Resumen Markdown privado NUEVO del dry-run}
        {--entrega= : Archivo NUEVO privado cifrado para accesos temporales}';

    protected $description = 'Altas DEMO por expediente, sin seeders ni pagos; aprobación, dry-run, auditoría y entrega cifrada';

    public function handle(AltasDemoEstudianteService $service, ConciliacionIdentidadService $identity): int
    {
        config(['cache.default' => 'array']);
        try {
            $path = (string) $this->argument('manifiesto');
            if ($this->option('preparar') && $this->option('aplicar')) {
                throw new RuntimeException('Preparar nunca permite aplicar simultáneamente.');
            }
            if ($this->option('preparar')) {
                $service->privateNewPath($path);
                $manifest = $this->readOnly(fn () => $service->propuesta());
                if (file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
                    throw new RuntimeException('No se pudo guardar la propuesta.');
                }
                @chmod($path, 0600);
            } else {
                $manifest = $this->read($path);
            }
            $sha = hash_file('sha256', $path);
            $receipt = $path.'.dry-run.json';
            if (! $this->option('aplicar')) {
                $rows = $this->readOnly(fn () => $service->ejecutar($manifest));
                if (file_put_contents($receipt, json_encode(['sha256' => $sha, 'destino' => $identity->destino(), 'fecha' => time()], JSON_THROW_ON_ERROR), LOCK_EX) === false) {
                    throw new RuntimeException('No se pudo guardar el comprobante de dry-run.');
                }
                @chmod($receipt, 0600);
                $this->info('DRY-RUN: '.count($rows).' filas verificadas, cero escrituras en BD. SHA256 '.$sha);
                $this->line('Evaluaciones permitidas propuestas: '.collect($manifest['filas'])->where('evaluacion_permitida', true)->count().'; restringidas: '.collect($manifest['filas'])->where('evaluacion_permitida', false)->count());
                if ($summary = $this->option('resumen')) {
                    $service->privateNewPath($summary);
                    $escape = fn ($value) => str_replace(["\r", "\n", '|'], [' ', ' ', '/'], (string) $value);
                    $md = "# Altas DEMO propuestas — pendientes de aprobación\n\n".$manifest['politica']."\n\nSHA256 del manifiesto: `".$sha."`\n\n";
                    $md .= "| id_post | Nombre existente | Correo existente | CI DEMO | Matrícula | Estado del expediente | Evaluación | Revisión |\n|---|---|---|---|---|---|---|---|\n";
                    foreach ($manifest['filas'] as $row) {
                        $md .= '| '.implode(' | ', array_map($escape, [$row['id_post'], $row['nombre'], $row['correo'], $row['documento'],
                            collect($row['matriculas'] ?? [])->pluck('codigo_mat')->implode(', '), $row['estado_postulante'] ?? '—',
                            ($row['evaluacion_permitida'] ?? false) ? 'permitida' : 'restringida', $row['revision']]))." |\n";
                    }
                    if (file_put_contents($summary, $md, LOCK_EX) === false) {
                        throw new RuntimeException('No se pudo escribir el resumen privado.');
                    }
                    @chmod($summary, 0600);
                }
            } else {
                $receiptData = $this->read($receipt);
                $backup = $this->read((string) $this->option('respaldo'));
                if (($receiptData['sha256'] ?? null) !== $sha || ($receiptData['destino'] ?? null) !== $identity->destino()
                    || ($receiptData['fecha'] ?? 0) < time() - 86400) {
                    throw new RuntimeException('Repite el dry-run del manifiesto exacto aprobado (caduca en 24 horas).');
                }
                if (($backup['destino'] ?? null) !== $identity->destino() || ($backup['restaurado_verificado'] ?? false) !== true
                    || ! is_file($backup['archivo'] ?? '') || ! hash_equals($backup['sha256'] ?? '', hash_file('sha256', $backup['archivo']))) {
                    throw new RuntimeException('Se requiere respaldo restaurado y verificado de esta conexión.');
                }
                $actorId = filter_var($this->option('actor'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $actor = $actorId ? User::find($actorId) : null;
                if (! $actor?->canChangeLoginEmail()) {
                    throw new RuntimeException('Indica el SA activo/verificado expresamente autorizado, sin actor por defecto.');
                }
                // Contexto CLI de ejecución autorizado; no simula una sesión web ni envía notificaciones.
                Auth::onceUsingId($actor->id);
                $service->authorize($actor, (string) $this->option('motivo'));
                if (! $this->confirm('¿Confirmas base DEMO y TODAS las altas de '.$sha.' en '.json_encode($identity->destino()).' como SA '.$actor->id.'?', false)) {
                    $this->warn('Cancelado sin altas.');

                    return self::FAILURE;
                }
                $rows = $service->ejecutar($manifest, true, $actor, (string) $this->option('motivo'), $this->option('entrega'));
                $this->info('Lote confirmado. Accesos temporales cifrados; no se muestran secretos.');
            }
            $this->table(['id_post', 'user_id', 'Resultado'], array_map('array_values', $rows));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            // QueryException puede incluir parámetros: no volcar excepciones ni credenciales.
            $this->error($exception instanceof RuntimeException && ! $exception instanceof QueryException
                ? $exception->getMessage() : 'Alta rechazada; no se confirmó el lote. Revisa validaciones y estado, sin volcar datos sensibles.');

            return self::FAILURE;
        } finally {
            if ($this->option('aplicar')) {
                Auth::forgetGuards();
            }
        }
    }

    private function read(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('Falta el manifiesto, comprobante o certificado de respaldo.');
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function readOnly(callable $operation): mixed
    {
        DB::beginTransaction();
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            }

            return $operation();
        } finally {
            DB::rollBack();
        }
    }
}
