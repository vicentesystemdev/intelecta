<?php

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
if ($app->configurationIsCached()) {
    throw new RuntimeException('Config cache no permitido.');
}
$app->make(Kernel::class)->bootstrap();
$database = DB::connection()->getDatabaseName();
if (! $app->environment('testing') || DB::getDriverName() !== 'pgsql'
    || ! preg_match('/^intelecta_accounts_test_[0-9_]+$/D', $database)
    || ($argv[1] ?? '') !== $database || DB::selectOne('SELECT current_database() AS name')->name !== $database) {
    throw new RuntimeException('Solo se permite una BD accounts_test aislada con nombre explícito y APP_ENV=testing.');
}
Notification::fake();
if (Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]) !== 0) {
    throw new RuntimeException('Falló instalación aislada.');
}
$passed = 0;
$assert = function (bool $ok, string $message) use (&$passed): void {
    if (! $ok) {
        throw new RuntimeException($message);
    }
    $passed++;
};
$assert(User::count() === 9, '9 usuarios demo.');
$assert(User::where('estado_cuenta', EstadoCuenta::ACTIVA->value)->whereNotNull('email_verified_at')->count() === 9, '9 cuentas activas y verificadas.');
$assert(User::role('Super Administrador')->where('estado_cuenta', 'activa')->count() === 1, 'Debe existir SA activo.');
$assert(Postulante::count() === 72, '72 postulantes.');
$assert(Postulante::whereNotNull('user_id')->count() === 3, '3 vínculos explícitos.');
$assert(Postulante::whereNull('user_id')->count() === 69, '69 expedientes sin cuenta.');
$assert(! Route::has('register'), 'No existe registro público.');
$assert(! Route::has('profile.destroy'), 'No existe autoeliminación.');
Notification::assertNothingSent();
$passed++;
foreach (User::role('Estudiante')->get() as $user) {
    $assert($user->postulante !== null, 'Cada estudiante demo conserva relación FK.');
}
echo json_encode(['database' => $database, 'passed' => $passed, 'failed' => 0, 'fresh_seed' => true, 'real_database_modified' => false]), PHP_EOL;
