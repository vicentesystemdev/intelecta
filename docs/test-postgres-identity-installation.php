<?php

// DESTRUCTIVE ONLY inside a separately created, explicitly selected identity TEST database.
// Refuse config cache and the project's real database; never infer or create a target.
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
if ($app->configurationIsCached()) {
    throw new RuntimeException('No se permite config cache en esta prueba aislada.');
}
$app->make(Kernel::class)->bootstrap();
$database = DB::connection()->getDatabaseName();
if (! $app->environment('testing') || DB::getDriverName() !== 'pgsql'
    || ! preg_match('/^intelecta_identity_test_[0-9_]+$/D', $database)
    || ($argv[1] ?? '') !== $database
    || DB::selectOne('SELECT current_database() AS name')->name !== $database) {
    throw new RuntimeException('Se requiere APP_ENV=testing, una BD PostgreSQL aislada intelecta_identity_test_<sufijo> y su nombre explícito como argumento.');
}

$exit = Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
if ($exit !== 0) {
    throw new RuntimeException(Artisan::output());
}
$assertions = 0;
$assert = function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (! $condition) {
        throw new RuntimeException($message);
    }
};
$assert(User::count() === 9, 'Deben existir 9 cuentas demo.');
$assert(Postulante::count() === 72, 'Deben existir 72 postulantes.');
$assert(Postulante::whereNotNull('user_id')->count() === 3, 'Deben existir 3 vínculos explícitos.');
$assert(Postulante::whereNull('user_id')->count() === 69, 'Deben existir 69 expedientes sin cuenta.');
foreach (User::role('Estudiante')->get() as $user) {
    $postulante = $user->postulante;
    $assert($postulante !== null, 'Cada estudiante demo debe tener FK.');
    DB::transaction(function () use ($user, $postulante, $assert): void {
        $postulante->update(['email_post' => 'contacto.'.$postulante->id_post.'@example.com']);
        $assert($user->postulante()->first()->is($postulante), 'Cambiar contacto no altera identidad.');
    });
}
echo json_encode(['database' => $database, 'migrate_fresh_seed_exit' => $exit, 'passed' => $assertions, 'failed' => 0, 'users' => 9, 'postulantes' => 72, 'linked' => 3, 'unlinked' => 69, 'real_database_modified' => false]), PHP_EOL;
