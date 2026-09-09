<?php

// Read real data; apply up/down and constraint probes ONLY to a TEMP table, then rollback.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('Se requiere PostgreSQL.');
}
$passed = 0;
$assert = function (bool $ok, string $message) use (&$passed): void {
    if (! $ok) {
        throw new RuntimeException($message);
    }
    $passed++;
};
$snapshot = fn () => DB::selectOne("SELECT count(*) AS total, md5(string_agg(row_to_json(u)::text,'' ORDER BY id)) AS fingerprint FROM public.users u");
$before = $snapshot();
$migration = require __DIR__.'/../database/migrations/2026_09_09_000000_add_account_lifecycle_to_users.php';
DB::beginTransaction();
try {
    DB::statement('CREATE TEMP TABLE users (id bigint PRIMARY KEY, email_verified_at timestamp NULL) ON COMMIT DROP');
    DB::table('pg_temp.users')->insert([['id' => 1, 'email_verified_at' => now()], ['id' => 2, 'email_verified_at' => null]]);
    $migration->up();
    $assert(DB::table('pg_temp.users')->where('id', 1)->value('estado_cuenta') === 'activa', 'Verificado debe migrar a activa.');
    $assert(DB::table('pg_temp.users')->where('id', 2)->value('estado_cuenta') === 'pendiente', 'Sin verificar debe migrar a pendiente.');
    $assert((int) DB::table('pg_temp.users')->max('version_acceso') === 0, 'Versiones iniciales deben ser cero.');
    DB::table('pg_temp.users')->insert(['id' => 3]);
    $assert(DB::table('pg_temp.users')->where('id', 3)->value('estado_cuenta') === 'pendiente', 'El default debe ser pendiente.');
    foreach ([['estado_cuenta' => 'invalido'], ['estado_cuenta' => null], ['estado_cuenta' => 'activa'], ['version_acceso' => -1]] as $values) {
        DB::statement('SAVEPOINT account_probe');
        $state = null;
        try {
            DB::table('pg_temp.users')->where('id', 2)->update($values);
        } catch (QueryException $exception) {
            $state = $exception->errorInfo[0] ?? null;
        } finally {
            DB::statement('ROLLBACK TO SAVEPOINT account_probe');
            DB::statement('RELEASE SAVEPOINT account_probe');
        }
        $assert(in_array($state, ['23514', '23502'], true), 'Debe rechazar estado/invariante/versión inválidos.');
    }
    DB::table('pg_temp.users')->where('id', 2)->update(['estado_cuenta' => 'bloqueada']);
    $assert(DB::table('pg_temp.users')->where('id', 2)->value('email_verified_at') === null, 'Bloqueada puede no estar verificada.');
    DB::table('pg_temp.users')->where('id', 1)->update(['estado_cuenta' => 'bloqueada']);
    $assert(DB::table('pg_temp.users')->where('id', 1)->value('email_verified_at') !== null, 'Bloqueada puede conservar verificación.');
    $migration->down();
    $migration->up();
    $assert(DB::table('pg_temp.users')->where('id', 1)->value('estado_cuenta') === 'activa', 'El rollback/up temporal debe funcionar.');
} finally {
    DB::rollBack();
}
$assert($before == $snapshot(), 'No se pueden modificar usuarios reales.');
echo json_encode(['passed' => $passed, 'failed' => 0, 'real_data_modified' => false]), PHP_EOL;
