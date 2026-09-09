<?php

// Execute the real additive migration against TEMP tables, never against public data.
// Explicit IDs and no sequences: even failing probes do not advance real sequences.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('Esta prueba requiere PostgreSQL.');
}
$assertions = 0;
$assert = function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (! $condition) {
        throw new RuntimeException($message);
    }
};
$snapshot = fn () => DB::selectOne("SELECT count(*) AS total, md5(string_agg(row_to_json(p)::text, '' ORDER BY id_post)) AS fingerprint FROM public.postulantes p");
$before = $snapshot();
$migration = require __DIR__.'/../database/migrations/2026_09_08_000000_add_user_id_to_postulantes.php';
DB::beginTransaction();
try {
    DB::statement('CREATE TEMP TABLE users (id bigint PRIMARY KEY, email varchar(255) UNIQUE) ON COMMIT DROP');
    DB::statement('CREATE TEMP TABLE postulantes (id_post bigint PRIMARY KEY, email_post varchar(255), deleted_at timestamp NULL) ON COMMIT DROP');
    DB::table('pg_temp.users')->insert([['id' => 1, 'email' => 'matching@example.com'], ['id' => 2, 'email' => 'different@example.com']]);
    DB::table('pg_temp.postulantes')->insert([['id_post' => 1, 'email_post' => 'matching@example.com'], ['id_post' => 2, 'email_post' => 'matching@example.com'], ['id_post' => 3, 'email_post' => null]]);
    $migration->up();
    $column = DB::selectOne("SELECT is_nullable, column_default, data_type FROM information_schema.columns WHERE table_schema = (SELECT nspname FROM pg_namespace WHERE oid = pg_my_temp_schema()) AND table_name = 'postulantes' AND column_name = 'user_id'");
    $assert($column->is_nullable === 'YES', 'user_id debe permitir NULL.');
    $assert($column->column_default === null, 'user_id no debe tener default.');
    $assert($column->data_type === 'bigint', 'user_id debe ser BIGINT.');
    $assert(DB::table('pg_temp.postulantes')->whereNull('user_id')->count() === 3, 'La migración NO debe vincular los correos coincidentes.');
    $fk = DB::selectOne("SELECT confdeltype, confrelid = 'pg_temp.users'::regclass AS temporary_target FROM pg_constraint WHERE conrelid = 'pg_temp.postulantes'::regclass AND contype = 'f'");
    $assert($fk->confdeltype === 'r' && $fk->temporary_target, 'La FK debe apuntar a users con DELETE RESTRICT.');
    DB::table('pg_temp.postulantes')->where('id_post', 1)->update(['user_id' => 1]);
    $assert((int) DB::table('pg_temp.postulantes')->where('id_post', 1)->value('user_id') === 1, 'Debe aceptar User válido.');
    $reject = function (callable $operation, string $state) use ($assert): void {
        $actual = null;
        DB::statement('SAVEPOINT identity_case');
        try {
            $operation();
        } catch (QueryException $exception) {
            $actual = $exception->errorInfo[0] ?? null;
        } finally {
            DB::statement('ROLLBACK TO SAVEPOINT identity_case');
            DB::statement('RELEASE SAVEPOINT identity_case');
        }
        $assert($actual === $state, 'SQLSTATE esperado '.$state.', recibido '.($actual ?? 'ninguno'));
    };
    $reject(fn () => DB::table('pg_temp.postulantes')->where('id_post', 2)->update(['user_id' => 1]), '23505');
    $reject(fn () => DB::table('pg_temp.postulantes')->where('id_post', 2)->update(['user_id' => 999]), '23503');
    $reject(fn () => DB::table('pg_temp.users')->where('id', 1)->delete(), '23503');
    $assert(DB::table('pg_temp.postulantes')->count() === 3, 'No debe eliminar expedientes.');
    DB::table('pg_temp.postulantes')->where('id_post', 1)->update(['deleted_at' => now()]);
    $reject(fn () => DB::table('pg_temp.postulantes')->where('id_post', 2)->update(['user_id' => 1]), '23505');
    $reject(fn () => DB::table('pg_temp.users')->where('id', 1)->delete(), '23503');
    DB::table('pg_temp.users')->where('id', 2)->delete();
    $assert(DB::table('pg_temp.postulantes')->whereNull('user_id')->count() === 2, 'Borrar una cuenta sin vínculo no afecta expedientes.');
    $migration->down();
    $migration->up();
    $assert(DB::table('pg_temp.postulantes')->whereNull('user_id')->count() === 3, 'El ciclo down/up no debe inferir vínculos.');
} finally {
    DB::rollBack();
}
$assert($before == $snapshot(), 'Los expedientes reales no deben cambiar.');
echo json_encode(['passed' => $assertions, 'failed' => 0, 'assertions' => $assertions, 'real_data_modified' => false], JSON_THROW_ON_ERROR), PHP_EOL;
