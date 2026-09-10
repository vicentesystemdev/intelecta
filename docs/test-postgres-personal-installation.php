<?php

// Destructive operations are restricted to an explicitly named, disposable Block 3 TEST database.
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Services\OrganizacionService;
use App\Domains\Institucional\Support\PermisosOrganizacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

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
    throw new RuntimeException('Solo se permite una BD personal_test aislada, explícita y APP_ENV=testing.');
}
$passed = 0;
$assert = function (bool $ok, string $message) use (&$passed): void {
    if (! $ok) {
        throw new RuntimeException($message);
    }
    $passed++;
};
Notification::fake();
$assert(Artisan::call('migrate:fresh', ['--force' => true]) === 0, 'Migración aislada.');
$assert(Schema::hasTable('cargos') && Schema::hasTable('personal_institucional'), 'Tablas nuevas.');
// Exercise only the last two migrations, on empty TEST tables, never the live database.
$assert(Artisan::call('migrate:rollback', ['--step' => 2, '--force' => true]) === 0, 'Rollback aislado.');
$assert(! Schema::hasTable('cargos') && ! Schema::hasTable('personal_institucional'), 'Rollback retira solo estructura del bloque.');
$assert(Schema::hasColumn('postulantes', 'user_id') && Schema::hasColumn('users', 'estado_cuenta'), 'Bloques previos conservados.');
$assert(Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]) === 0, 'Instalación limpia con seed.');
$assert(Cargo::count() === 8 && Cargo::where('estado', 'activo')->count() === 8, 'Ocho cargos activos.');
$assert(PersonalInstitucional::count() === 5 && PersonalInstitucional::whereNotNull('user_id')->count() === 5, 'Cinco perfiles demo explícitos.');
$assert(User::count() === 9 && User::where('estado_cuenta', 'activa')->whereNotNull('email_verified_at')->count() === 9, 'Nueve cuentas demo activas/verificadas.');
$sa = User::role('Super Administrador')->sole();
$assert($sa->personalInstitucional === null, 'TI no requiere Personal.');
$admin = User::role('Administrador')->sole();
$assert($admin->personalInstitucional->cargo->nombre_cargo === 'Coordinador Académico', 'Cargo explícito del fixture administrativo.');
$assert(User::role('Estudiante')->whereHas('personalInstitucional')->count() === 0, 'Estudiantes no convertidos en Personal.');
$assert(Postulante::count() === 72 && Postulante::whereNotNull('user_id')->count() === 3 && Postulante::whereNull('user_id')->count() === 69, 'Bloque 1 intacto.');
$assert(TutorAcademico::count() === 4 && ! Schema::hasColumn('tutores_academicos', 'personal_id'), 'Tutor mantiene esquema anterior.');
$assert(! Route::has('register') && ! Route::has('profile.destroy'), 'Registro y autoeliminación siguen cerrados.');
foreach (PermisosOrganizacion::ALL as $permission) {
    $assert(Role::findByName('Administrador')->hasPermissionTo($permission) && ! Role::findByName('Docente')->hasPermissionTo($permission), 'Permiso organizacional limitado: '.$permission);
}
Notification::assertNothingSent();
$passed++;

DB::beginTransaction();
try {
    $cargo = Cargo::where('nombre_cargo', 'Secretaría')->sole();
    $record = PersonalInstitucional::factory()->create(['user_id' => $sa->id, 'cargo_id' => $cargo->id_cargo, 'ci' => '1234567']);
    foreach ([
        ['23505', fn () => DB::table('cargos')->insert(['nombre_cargo' => 'SECRETARÍA'])],
        ['23514', fn () => DB::table('cargos')->where('id_cargo', $cargo->id_cargo)->update(['estado' => 'pendiente'])],
        ['23502', fn () => DB::table('cargos')->where('id_cargo', $cargo->id_cargo)->update(['estado' => null])],
        ['23514', fn () => DB::table('personal_institucional')->where('id_personal', $record->id_personal)->update(['estado' => 'bloqueada'])],
        ['23502', fn () => DB::table('personal_institucional')->where('id_personal', $record->id_personal)->update(['estado' => null])],
        ['23503', fn () => DB::table('personal_institucional')->where('id_personal', $record->id_personal)->update(['user_id' => 999999])],
        ['23503', fn () => DB::table('personal_institucional')->where('id_personal', $record->id_personal)->update(['cargo_id' => 999999])],
        ['23505', fn () => PersonalInstitucional::factory()->create(['user_id' => $sa->id])],
        ['23505', fn () => PersonalInstitucional::factory()->create(['ci' => '1234567'])],
        ['23503', fn () => DB::table('users')->where('id', $sa->id)->delete()],
        ['23503', fn () => DB::table('cargos')->where('id_cargo', $cargo->id_cargo)->delete()],
    ] as [$expected, $probe]) {
        $state = null;
        try {
            DB::transaction($probe);
        } catch (QueryException $exception) {
            $state = $exception->errorInfo[0] ?? null;
        }
        $assert($state === $expected, 'Constraint PostgreSQL '.$expected);
    }
    PersonalInstitucional::factory()->count(2)->create();
    $assert(PersonalInstitucional::whereNull('user_id')->whereNull('cargo_id')->whereNull('ci')->count() === 2, 'Múltiples NULL permitidos.');
    $service = app(OrganizacionService::class);
    $before = $sa->fresh()->getRawOriginal();
    $service->changeState($cargo, EstadoCargo::INACTIVO, $sa);
    $assert($record->fresh()->cargo_id === $cargo->id_cargo, 'Inactivar conserva relación.');
    $service->savePersonal(['nombres' => 'Nombre Actualizado', 'cargo_id' => $cargo->id_cargo], $sa, $record);
    $assert($record->fresh()->cargo_id === $cargo->id_cargo, 'Editar conserva cargo inactivo actual.');
    $rejected = false;
    try {
        $service->savePersonal(['nombres' => 'Persona Nueva', 'apellidos' => 'Prueba', 'cargo_id' => $cargo->id_cargo], $sa);
    } catch (ValidationException $exception) {
        $rejected = isset($exception->errors()['cargo_id']);
    }
    $assert($rejected, 'No admite nueva asignación inactiva.');
    $assert($sa->fresh()->getRawOriginal() === $before, 'Estado/cargo de Personal no modifica User.');
} finally {
    DB::rollBack();
}
$assert(PersonalInstitucional::count() === 5 && Cargo::where('estado', 'activo')->count() === 8, 'Fixtures restaurados tras pruebas transaccionales.');
echo json_encode(['database' => $database, 'passed' => $passed, 'failed' => 0, 'fresh_seed' => true, 'rollback_verified' => true, 'real_database_modified' => false]), PHP_EOL;
