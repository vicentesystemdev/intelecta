<?php

// Only an explicit disposable Block 4 database. Never run against intelecta.
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Repositories\TutorAcademicoRepository;
use App\Domains\Academico\Services\MigrarIdentidadTutorService;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

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
    || ! preg_match('/^intelecta_tutor_test_[0-9_]+$/D', $database)
    || ($argv[1] ?? '') !== $database || DB::selectOne('SELECT current_database() AS name')->name !== $database) {
    throw new RuntimeException('Solo una BD tutor_test aislada y explícita con APP_ENV=testing.');
}
$passed = 0;
$assert = function (bool $ok, string $message) use (&$passed): void {
    if (! $ok) {
        throw new RuntimeException($message);
    }
    $passed++;
};
$fingerprint = fn ($table) => DB::selectOne('SELECT count(*) AS count, md5(string_agg(row_to_json(t)::text, \'\' ORDER BY row_to_json(t)::text)) AS hash FROM "'.$table.'" t');
$protected = ['users', 'roles', 'permissions', 'model_has_roles', 'role_has_permissions', 'postulantes', 'asignaciones_tutores', 'asistencias_academicas', 'cargos'];
$before = [];
foreach ($protected as $table) {
    $before[$table] = $fingerprint($table);
}
Notification::fake();

// A restored legacy snapshot or repeated execution after this script's clean installation.
if (! Schema::hasColumn('tutores_academicos', 'user_id')) {
    $assert(Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]) === 0, 'Volver a transición en TEST.');
}
if (! Schema::hasColumn('tutores_academicos', 'personal_id')) {
    $assert(Artisan::call('migrate', ['--path' => 'database/migrations/2026_09_11_000000_add_personal_id_to_tutores_academicos.php', '--force' => true]) === 0, 'Migración aditiva.');
}
$nullable = fn () => DB::selectOne("SELECT is_nullable FROM information_schema.columns WHERE table_schema='public' AND table_name='tutores_academicos' AND column_name='personal_id'")->is_nullable;
$assert($nullable() === 'YES', 'Nullable durante transición.');
$legacy = DB::table('tutores_academicos')->orderBy('id_tutor')->get();
$assert($legacy->count() === 4, 'Cuatro tutores en snapshot/fixture.');
$service = app(MigrarIdentidadTutorService::class);
$assert($service->execute()['conflictos'] === 0, 'Dry-run sin conflictos.');
$assert($service->execute(false)['conflictos'] === 0, 'Migración aplicada.');
$peopleHash = $fingerprint('personal_institucional');
$assert($service->execute(false)['conflictos'] === 0 && $peopleHash == $fingerprint('personal_institucional'), 'Idempotencia sin alterar timestamps.');
foreach ($legacy as $old) {
    $tutor = DB::table('tutores_academicos')->where('id_tutor', $old->id_tutor)->first();
    $person = DB::table('personal_institucional')->where('id_personal', $tutor->personal_id)->first();
    $assert($old->user_id === $person->user_id, 'Preserva User estructural.');
    foreach (MigrarIdentidadTutorService::FIELDS as $source => $target) {
        $assert($old->{$source} === $person->{$target}, 'Conservación exacta de '.$target);
    }
    $oldValues = (array) $old;
    $newValues = (array) $tutor;
    unset($oldValues['personal_id'], $newValues['personal_id']);
    $assert($oldValues === $newValues, 'Tutor intacto salvo FK añadida.');
}
$assert(Artisan::call('migrate', ['--path' => 'database/migrations/2026_09_11_000100_finalize_tutor_personal_identity.php', '--force' => true]) === 0, 'Retiro de columnas tras verificar.');
$assert($nullable() === 'NO', 'NOT NULL definitivo.');
foreach (['user_id', ...array_keys(MigrarIdentidadTutorService::FIELDS)] as $field) {
    $assert(! Schema::hasColumn('tutores_academicos', $field), 'Retirada '.$field);
}
foreach ($protected as $table) {
    $assert($before[$table] == $fingerprint($table), 'Sin cambios en '.$table);
}
$constraints = DB::select("SELECT pg_get_constraintdef(oid) AS def FROM pg_constraint WHERE conrelid='tutores_academicos'::regclass");
$definitions = implode("\n", array_column($constraints, 'def'));
$assert(str_contains($definitions, 'FOREIGN KEY (personal_id) REFERENCES personal_institucional(id_personal) ON DELETE RESTRICT'), 'FK RESTRICT real.');
$assert(str_contains($definitions, 'UNIQUE (personal_id)'), 'Unicidad real, también archivados.');

$tutor = TutorAcademico::firstOrFail();
foreach ([
    ['23502', fn () => DB::table('tutores_academicos')->insert(['estado_tutor' => 'activo'])],
    ['23503', fn () => DB::table('tutores_academicos')->insert(['personal_id' => 999999999, 'estado_tutor' => 'activo'])],
    ['23505', fn () => DB::table('tutores_academicos')->insert(['personal_id' => $tutor->personal_id, 'estado_tutor' => 'activo'])],
    ['23503', fn () => DB::table('personal_institucional')->where('id_personal', $tutor->personal_id)->delete()],
] as [$state, $operation]) {
    try {
        DB::transaction($operation);
        throw new RuntimeException('Faltó restricción '.$state);
    } catch (QueryException $exception) {
        $assert($exception->getCode() === $state, 'SQLSTATE '.$state);
    }
}
$tutor->delete();
$assert($tutor->personal->tutorAcademico->trashed(), 'Reserva de tutor archivado.');
$assert(! app(TutorAcademicoRepository::class)->personalOptions()->contains('id_personal', $tutor->personal_id), 'Archivado no elegible.');
$assignment = AsignacionTutor::where('id_tutor', $tutor->id_tutor)->first();
$assert($assignment && $assignment->tutor->nombre_completo === $tutor->nombre_completo, 'Historial muestra tutor archivado.');
$tutor->restore();

$assert(Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]) === 0, 'Rollback final aislado.');
$assert(Schema::hasColumn('tutores_academicos', 'nombres_tutor') && $nullable() === 'YES', 'Rollback reconstruye snapshot de identidad actual.');
$assert(Artisan::call('migrate', ['--force' => true]) === 0, 'Reaplicación final aislada.');
$assert(Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]) === 0, 'Instalación limpia aislada.');
$assert(Cargo::count() === 8, 'Ocho cargos demo.');
$assert(PersonalInstitucional::count() === 5, 'Cinco personas demo.');
$assert(TutorAcademico::count() === 4 && TutorAcademico::whereNotNull('personal_id')->count() === 4, 'Cuatro tutores explícitos.');
$assert(User::count() === 9 && User::where('estado_cuenta', 'activa')->whereNotNull('email_verified_at')->count() === 9, 'Cuentas activas verificadas.');
foreach (TutorAcademico::with('personal.user', 'personal.cargo')->get() as $record) {
    $assert($record->personal->user->hasRole('Docente'), 'Referencia explícita al docente fixture.');
    $assert($record->personal->cargo->nombre_cargo === 'Docente', 'Cargo explícito del fixture, no inferido en datos reales.');
}
$assert(DB::table('postulantes')->whereNotNull('user_id')->count() === 3, 'Identidad estudiantil del fixture.');
$assert(AsignacionTutor::count() === 16, 'Asignaciones demo conservan id_tutor.');
$assert(AsistenciaAcademica::count() === 544, 'Asistencias demo.');
foreach (AsignacionTutor::with('tutor')->get() as $record) {
    $assert(filled($record->tutor->nombre_completo), 'Asignación presenta identidad.');
}
$assert(filled(AsistenciaAcademica::with('tutor')->whereNotNull('id_tutor')->firstOrFail()->tutor->nombre_completo), 'Asistencia presenta identidad.');
$page = app(TutorAcademicoRepository::class)->paginate(['buscar' => 'Rodrigo']);
$assert($page->total() === 1 && $page->items()[0]->nombre_completo === 'Rodrigo Salazar Condori', 'Búsqueda PostgreSQL por Personal.');
$assert(app(TutorAcademicoRepository::class)->paginate(['buscar' => '7300101'])->total() === 1, 'Búsqueda CI PostgreSQL.');
$assert(app(TutorAcademicoRepository::class)->paginate(['buscar' => 'rodrigo.salazar@avalancha.edu.bo'])->total() === 1, 'Búsqueda contacto PostgreSQL.');
Notification::assertNothingSent();
$passed++;
echo json_encode(['database' => $database, 'passed' => $passed, 'failed' => 0, 'notifications' => 0], JSON_PRETTY_PRINT), PHP_EOL;
