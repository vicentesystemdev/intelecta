<?php

use App\Domains\Institucional\Actions\VincularUsuarioPersonalAction;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Services\OrganizacionService;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
if ($app->configurationIsCached()) {
    throw new RuntimeException('Config cache no permitido.');
}
$app->make(Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $exception): void {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
});
$database = DB::connection()->getDatabaseName();
if (! $app->environment('testing') || DB::getDriverName() !== 'pgsql'
    || ! preg_match('/^intelecta_personal_test_[0-9_]+$/D', $database)
    || ($argv[1] ?? '') !== $database || DB::selectOne('SELECT current_database() AS name')->name !== $database) {
    throw new RuntimeException('Solo se permite una BD personal_test explícita.');
}
if (($argv[2] ?? '') === 'worker') {
    $actor = User::findOrFail((int) $argv[3]);
    echo "READY\n";
    flush();
    try {
        if ($argv[4] === 'link') {
            app(VincularUsuarioPersonalAction::class)->execute((int) $argv[5], (int) $argv[6], $actor, 'Confirmación concurrente de prueba');
        } else {
            app(OrganizacionService::class)->savePersonal(['cargo_id' => (int) $argv[5]], $actor, PersonalInstitucional::findOrFail((int) $argv[6]));
        }
        echo "ACCEPTED\n";
    } catch (ValidationException $exception) {
        if (! isset($exception->errors()[$argv[4] === 'link' ? 'user_id' : 'cargo_id'])) {
            throw $exception;
        }
        echo "REJECTED_CONFLICT\n";
    }
    exit(0);
}
$actor = User::role('Super Administrador')->where('estado_cuenta', 'activa')->firstOrFail();
$makeWorker = fn (string $operation, int $target, int $personal) => (new Process([PHP_BINARY, __FILE__, $database, 'worker', (string) $actor->id, $operation, (string) $target, (string) $personal], dirname(__DIR__)))->setTimeout(45);
$waitReady = function (array $workers): void {
    $deadline = microtime(true) + 20;
    while (count(array_filter($workers, fn ($worker) => str_contains($worker->getOutput(), 'READY'))) !== count($workers)) {
        if (microtime(true) > $deadline) {
            throw new RuntimeException('Workers no llegaron a la barrera.');
        }
        usleep(20000);
    }
};
$passed = 0;
foreach (['same_user', 'same_personal'] as $scenario) {
    $users = [User::factory()->pending()->create(), User::factory()->blocked()->create()];
    $people = [PersonalInstitucional::factory()->create(), PersonalInstitucional::factory()->create()];
    $workers = [
        $makeWorker('link', $users[0]->id, $people[0]->id_personal),
        $makeWorker('link', $users[$scenario === 'same_user' ? 0 : 1]->id, $people[$scenario === 'same_personal' ? 0 : 1]->id_personal),
    ];
    DB::select('SELECT pg_advisory_lock(?)', [CuentaService::LOCK_KEY]);
    try {
        foreach ($workers as $worker) {
            $worker->start();
        }
        $waitReady($workers);
    } finally {
        DB::select('SELECT pg_advisory_unlock(?)', [CuentaService::LOCK_KEY]);
    }
    try {
        foreach ($workers as $worker) {
            $worker->wait();
            if (! $worker->isSuccessful()) {
                throw new RuntimeException('Falló un worker de identidad.');
            }
        }
        if (count(array_filter($workers, fn ($worker) => str_contains($worker->getOutput(), 'ACCEPTED'))) !== 1
            || count(array_filter($workers, fn ($worker) => str_contains($worker->getOutput(), 'REJECTED_CONFLICT'))) !== 1
            || PersonalInstitucional::whereIn('id_personal', array_map(fn ($person) => $person->id_personal, $people))->whereNotNull('user_id')->count() !== 1) {
            throw new RuntimeException('No se preservó la cardinalidad de identidad.');
        }
        $passed++;
    } finally {
        foreach ($workers as $worker) {
            if ($worker->isRunning()) {
                $worker->stop();
            }
        }
    }
}

$cargo = Cargo::factory()->create();
$person = PersonalInstitucional::factory()->create();
$worker = $makeWorker('assign', $cargo->id_cargo, $person->id_personal);
DB::beginTransaction();
try {
    DB::table('cargos')->where('id_cargo', $cargo->id_cargo)->update(['estado' => 'inactivo']);
    $worker->start();
    $waitReady([$worker]);
    $deadline = microtime(true) + 20;
    do {
        DB::select('SELECT pg_stat_clear_snapshot()');
        $waiting = DB::selectOne("SELECT count(*) AS total FROM pg_stat_activity WHERE datname = current_database() AND pid <> pg_backend_pid() AND wait_event_type = 'Lock' AND query LIKE '%cargos%'")->total;
        if (microtime(true) > $deadline) {
            throw new RuntimeException('No se observó el bloqueo de la asignación concurrente.');
        }
        usleep(20000);
    } while ((int) $waiting === 0);
    DB::commit();
    $worker->wait();
    if (! $worker->isSuccessful() || ! str_contains($worker->getOutput(), 'REJECTED_CONFLICT') || $person->fresh()->cargo_id !== null) {
        throw new RuntimeException('La inactivación concurrente permitió una nueva asignación.');
    }
    $passed++;
} finally {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    if ($worker->isRunning()) {
        $worker->stop();
    }
}
echo json_encode(['database' => $database, 'concurrent_scenarios_passed' => $passed, 'failed' => 0, 'real_database_modified' => false]), PHP_EOL;
