<?php

// Run only on the explicitly named restored Block 5 test database, never on intelecta.
use App\Domains\Seguridad\Services\DesplegarMatrizRbac;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
if ($app->configurationIsCached()) {
    throw new RuntimeException('No se permite configuración cacheada.');
}
$app->make(Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $exception): void {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
});
$database = DB::connection()->getDatabaseName();
if (! $app->environment('testing') || DB::getDriverName() !== 'pgsql'
    || ! preg_match('/^intelecta_rbac_restore_[0-9_]+$/D', $database) || ($argv[1] ?? '') !== $database
    || DB::selectOne('SELECT current_database() AS name')->name !== $database) {
    throw new RuntimeException('Se requiere rbac_restore aislada, explícita y APP_ENV=testing.');
}
$checks = 0;
$assert = function (bool $value, string $reason) use (&$checks): void {
    if (! $value) {
        throw new RuntimeException($reason);
    }
    $checks++;
};
$hashes = function (): array {
    $result = [];
    foreach (DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename") as $row) {
        $result[$row->tablename] = (array) DB::selectOne('SELECT count(*) AS count, md5(string_agg(row_to_json(t)::text, \'\' ORDER BY row_to_json(t)::text)) AS hash FROM "'.$row->tablename.'" t');
    }

    return $result;
};
$service = app(DesplegarMatrizRbac::class);
$before = $hashes();
$snapshot = sys_get_temp_dir().'/'.$database.'-'.bin2hex(random_bytes(8)).'.json';
$assert($service->apply($snapshot), 'Aplicación efectiva.');
$assert($service->matrix($service->state()) === $service->target(), 'Matriz objetivo exacta.');
$assert(DB::table('role_has_permissions')->count() === 137, '137 pivotes.');
$after = $hashes();
foreach ($before as $table => $hash) {
    if (! in_array($table, ['permissions', 'role_has_permissions'], true)) {
        $assert($hash === $after[$table], 'Preservación tras apply: '.$table);
    }
}
$assert(! $service->apply($snapshot), 'Idempotencia.');
$assert($after === $hashes(), 'Idempotencia sin alterar filas.');
$assert($service->restore($snapshot), 'Rollback efectivo.');
foreach ($before as $table => $hash) {
    $assert($hash === $hashes()[$table], 'Rollback exacto: '.$table);
}
$assert(! $service->restore($snapshot), 'Rollback idempotente.');
$assert($service->apply($snapshot.'.reapply'), 'Reaplicación efectiva.');
$assert($service->matrix($service->state()) === $service->target(), 'Reaplicación exacta.');
$assert(DB::table('model_has_permissions')->count() === 0, 'Sin permisos directos.');
echo json_encode(['database' => $database, 'checks' => $checks, 'failed' => 0, 'snapshot' => $snapshot, 'main_modified' => false]), PHP_EOL;
