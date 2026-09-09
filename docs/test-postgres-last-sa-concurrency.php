<?php

// Two independent PHP workers use the REAL service against an explicitly isolated TEST DB.
use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

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
    throw new RuntimeException('Se requiere BD accounts_test aislada y explícita.');
}
if (($argv[2] ?? '') === 'worker') {
    $user = User::findOrFail((int) $argv[3]);
    echo "READY\n";
    flush();
    try {
        $accounts = app(CuentaService::class);
        if ($argv[4] === 'block') {
            $accounts->block($user, $user->id, 'Prueba concurrente en base aislada');
        } else {
            $accounts->update($user, $user->id, ['name' => 'Responsable Prueba', 'email' => $argv[4] === 'email' ? 'cambio.'.$user->id.'@example.com' : $user->email, 'role' => $argv[4] === 'demote' ? 'Administrador' : 'Super Administrador']);
        }
        echo "ACCEPTED\n";
    } catch (ValidationException $exception) {
        if (! isset($exception->errors()['ultimo_sa'])) {
            throw $exception;
        }
        echo "REJECTED_LAST_SA\n";
    }
    exit(0);
}
$first = User::role('Super Administrador')->firstOrFail();
$second = User::create(['name' => 'Responsable Concurrencia', 'email' => 'concurrencia.'.$first->id.'@example.com', 'password' => Str::random(64)]);
$second->forceFill(['estado_cuenta' => EstadoCuenta::ACTIVA, 'email_verified_at' => now()])->save();
$second->assignRole('Super Administrador');
$passed = 0;
foreach ([['block', 'block'], ['demote', 'demote'], ['block', 'demote'], ['email', 'email']] as $operations) {
    foreach ([$first, $second] as $user) {
        $user->refresh()->forceFill(['estado_cuenta' => EstadoCuenta::ACTIVA, 'email_verified_at' => now()])->save();
        $user->syncRoles(['Super Administrador']);
    }
    // A parent-held session advisory lock is the common start barrier for both workers.
    DB::select('SELECT pg_advisory_lock(?)', [CuentaService::LOCK_KEY]);
    $workers = [];
    try {
        foreach ([$first, $second] as $i => $user) {
            $workers[] = (new Process([PHP_BINARY, __FILE__, $database, 'worker', (string) $user->id, $operations[$i]], dirname(__DIR__)))->setTimeout(40);
        }
        foreach ($workers as $worker) {
            $worker->start();
        }
        $deadline = microtime(true) + 20;
        do {
            $ready = count(array_filter($workers, fn (Process $worker) => str_contains($worker->getOutput(), 'READY')));
            if (microtime(true) > $deadline) {
                throw new RuntimeException('Timeout esperando workers.');
            }
            usleep(20000);
        } while ($ready !== 2);
    } finally {
        DB::select('SELECT pg_advisory_unlock(?)', [CuentaService::LOCK_KEY]);
    }
    $accepted = 0;
    $rejected = 0;
    foreach ($workers as $worker) {
        $worker->wait();
        if (! $worker->isSuccessful()) {
            throw new RuntimeException('Falló worker concurrente.');
        }
        $accepted += str_contains($worker->getOutput(), 'ACCEPTED') ? 1 : 0;
        $rejected += str_contains($worker->getOutput(), 'REJECTED_LAST_SA') ? 1 : 0;
    }
    if ($accepted !== 1 || $rejected !== 1 || User::role('Super Administrador')->where('estado_cuenta', 'activa')->whereNotNull('email_verified_at')->count() !== 1) {
        throw new RuntimeException('No se preservó exactamente un SA activo.');
    }
    $passed++;
}
echo json_encode(['database' => $database, 'concurrent_scenarios_passed' => $passed, 'failed' => 0, 'real_database_modified' => false]), PHP_EOL;
