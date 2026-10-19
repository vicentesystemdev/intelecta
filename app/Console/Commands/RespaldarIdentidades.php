<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;

class RespaldarIdentidades extends Command
{
    protected $signature = 'intelecta:respaldar-identidades {archivo : Archivo .dump NUEVO en directorio privado existente}
        {--pg-bin= : Directorio de ejecutables PostgreSQL}
        {--verificar-puerto= : Puerto de una instancia PostgreSQL AISLADA distinta del origen}
        {--verificar-usuario= : Usuario de la instancia aislada}
        {--verificar-bd= : Base NUEVA con prefijo intelecta_isolated_}';

    protected $description = 'Respalda PostgreSQL y verifica restaurando en otra instancia local, sin modificar el origen';

    public function handle(): int
    {
        $source = DB::connection();
        $port = filter_var($this->option('verificar-puerto'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        $database = (string) $this->option('verificar-bd');
        $file = (string) $this->argument('archivo');
        if ($source->getDriverName() !== 'pgsql' || ! $port || $port === (int) $source->getConfig('port')
            || ! preg_match('/^intelecta_isolated_[a-z0-9_]+$/', $database)
            || ! $this->option('verificar-usuario') || file_exists($file) || file_exists($file.'.verified.json')
            || ! is_dir(dirname($file))) {
            throw new RuntimeException('Se requiere archivo nuevo, directorio existente y otra instancia/puerto con base aislada nueva.');
        }
        $bin = rtrim((string) $this->option('pg-bin'), '/\\');
        $program = fn ($name) => $bin.DIRECTORY_SEPARATOR.$name.(PHP_OS_FAMILY === 'Windows' ? '.exe' : '');
        foreach (['pg_dump', 'createdb', 'pg_restore'] as $name) {
            if (! is_file($program($name))) {
                throw new RuntimeException('Falta el ejecutable PostgreSQL '.$name);
            }
        }
        $source->beginTransaction();
        try {
            $source->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            $snapshot = $source->selectOne('SELECT pg_export_snapshot() AS snapshot')->snapshot;
            $before = $this->fingerprints($source);
            $this->info('Generando respaldo con snapshot de solo lectura...');
            $this->runProcess([$program('pg_dump'), '--host='.$source->getConfig('host'), '--port='.$source->getConfig('port'),
                '--username='.$source->getConfig('username'), '--dbname='.$source->getConfig('database'), '--no-password',
                '--format=custom', '--snapshot='.$snapshot, '--file='.$file],
                ['PGPASSWORD' => (string) $source->getConfig('password'), 'PGOPTIONS' => '-c default_transaction_read_only=on']);
        } finally {
            $source->rollBack();
        }
        @chmod($file, 0600);
        $targetArgs = ['--host=127.0.0.1', '--port='.$port, '--username='.$this->option('verificar-usuario'), '--no-password'];
        $this->info('Restaurando en instancia aislada '.$port.' / '.$database.' (sin migraciones)...');
        $this->runProcess([$program('createdb'), ...$targetArgs, $database], ['PGPASSWORD' => '', 'PGOPTIONS' => '']);
        $this->runProcess([$program('pg_restore'), ...$targetArgs, '--dbname='.$database, '--no-owner', '--no-privileges', '--exit-on-error', $file], ['PGPASSWORD' => '', 'PGOPTIONS' => '']);
        config(['database.connections.identity_backup_verification' => [
            ...$source->getConfig(), 'url' => null, 'host' => '127.0.0.1', 'port' => $port,
            'database' => $database, 'username' => $this->option('verificar-usuario'), 'password' => '',
        ]]);
        DB::purge('identity_backup_verification');
        $target = DB::connection('identity_backup_verification');
        $target->beginTransaction();
        try {
            $target->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            $after = $this->fingerprints($target);
        } finally {
            $target->rollBack();
            DB::disconnect('identity_backup_verification');
        }
        if ($before !== $after) {
            throw new RuntimeException('La restauración difiere del snapshot. No se certifica ni se autoriza conciliación.');
        }
        $metadata = ['destino' => collect($source->getConfig())->only(['driver', 'host', 'port', 'database'])->all(),
            'archivo' => realpath($file), 'sha256' => hash_file('sha256', $file), 'fecha' => now()->toIso8601String(),
            'restaurado_verificado' => true, 'restauracion' => ['host' => '127.0.0.1', 'port' => $port, 'database' => $database],
            'tablas_verificadas' => $before];
        if (file_put_contents($file.'.verified.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
            throw new RuntimeException('No se pudo guardar el certificado; el respaldo no queda certificado.');
        }
        @chmod($file.'.verified.json', 0600);
        $this->info('Restauración íntegra: '.count($before).' tablas coinciden. SHA256 '.$metadata['sha256']);
        $this->line('Certificado privado: '.$file.'.verified.json');

        return self::SUCCESS;
    }

    private function runProcess(array $command, array $env): void
    {
        (new Process($command, env: $env, timeout: 300))->mustRun();
    }

    private function fingerprints(Connection $connection): array
    {
        $result = [];
        foreach ($connection->select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename") as $table) {
            $name = $connection->getQueryGrammar()->wrapTable($table->tablename);
            // Orden binario estable aunque las dos instancias usen distintas collations.
            $row = $connection->selectOne("SELECT count(*) AS n, md5(coalesce(string_agg(row_to_json(t)::text, '' ORDER BY row_to_json(t)::text COLLATE \"C\"), '')) AS hash FROM {$name} t");
            $result[$table->tablename] = ['filas' => (int) $row->n, 'md5_contenido' => $row->hash];
        }

        return $result;
    }
}
