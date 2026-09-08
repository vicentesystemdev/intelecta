<?php

// PostgreSQL round-trip against a temporary clone: never writes real rows or advances real sequences.
use App\Domains\Postulantes\DTOs\PostulanteData;
use App\Domains\Postulantes\Models\Postulante;
use App\Http\Requests\Postulantes\StorePostulanteRequest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('Esta prueba requiere PostgreSQL.');
}
$started = microtime(true);
$assertions = 0;
$assert = function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (! $condition) {
        throw new RuntimeException($message);
    }
};
$before = DB::table('postulantes')->orderBy('id_post')->get(['id_post', 'edad_post', 'fecha_nacimiento_post']);
$column = DB::selectOne("SELECT data_type, is_nullable FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = 'postulantes' AND column_name = 'fecha_nacimiento_post'");
$assert($column?->data_type === 'date', 'La columna debe ser DATE.');
$assert($column?->is_nullable === 'YES', 'La columna debe permitir NULL para legacy.');
$check = DB::selectOne("SELECT convalidated, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE conrelid = 'postulantes'::regclass AND conname = 'intelecta_postulantes_edad_check'");
$assert((bool) $check?->convalidated, 'El CHECK histórico debe seguir validado.');
DB::beginTransaction();
try {
    DB::statement('CREATE TEMP TABLE intelecta_birth_date_probe ON COMMIT DROP AS SELECT * FROM postulantes WITH NO DATA');
    $request = StorePostulanteRequest::create('/', 'POST', [
        'nombres_post' => 'Prueba Fecha',
        'apellidos_post' => 'Calendario',
        'fecha_nacimiento_post' => '15/08/2005',
        'edad_post' => 80,
        'gestion_post' => now()->year,
        'estado_post' => 'activo',
    ]);
    $request->setContainer($app)->setRedirector($app['redirect']);
    $request->setUserResolver(fn () => new class
    {
        public function can(string $permission): bool
        {
            return true;
        }
    });
    $request->validateResolved();
    $assert($request->validated('fecha_nacimiento_post') === '2005-08-15', 'Laravel debe normalizar DD/MM/AAAA.');
    $assert(! array_key_exists('edad_post', $request->validated()), 'No debe confiar en edad enviada por el cliente.');
    $person = new Postulante;
    $person->setTable('pg_temp.intelecta_birth_date_probe');
    $person->fill(PostulanteData::fromArray($request->validated())->toArray());
    $person->id_post = -1;
    $person->save();
    $person->refresh();
    $stored = DB::selectOne('SELECT fecha_nacimiento_post::text AS value, pg_typeof(fecha_nacimiento_post)::text AS type FROM pg_temp.intelecta_birth_date_probe WHERE id_post = -1');
    $assert($stored->value === '2005-08-15', 'PostgreSQL debe almacenar 2005-08-15.');
    $assert($stored->type === 'date', 'El valor almacenado debe ser DATE.');
    $assert($person->toArray()['fecha_nacimiento_post'] === '2005-08-15', 'La serialización no debe añadir hora.');
    $assert($person->edad_post === null, 'No debe almacenarse una edad manual para registros nuevos.');
    $script = "import { formatDateLatam } from './resources/js/lib/dateOnly.js'; process.stdout.write(formatDateLatam(process.argv[1]));";
    $process = new Process([$argv[1] ?? 'node', '--input-type=module', '-e', $script, $person->toArray()['fecha_nacimiento_post']], dirname(__DIR__));
    $process->mustRun();
    $assert($process->getOutput() === '15/08/2005', 'El helper usado por React debe mostrar 15/08/2005.');
} finally {
    DB::rollBack();
}
$after = DB::table('postulantes')->orderBy('id_post')->get(['id_post', 'edad_post', 'fecha_nacimiento_post']);
$assert($before->toJson() === $after->toJson(), 'Los datos reales no deben cambiar.');
echo json_encode([
    'passed' => $assertions, 'failed' => 0, 'assertions' => $assertions,
    'duration_ms' => round((microtime(true) - $started) * 1000),
    'input_ui' => '15/08/2005', 'postgres_date' => $stored->value, 'output_ui' => $process->getOutput(),
    'legacy_null_dates' => $after->whereNull('fecha_nacimiento_post')->count(),
    'real_data_modified' => false,
], JSON_THROW_ON_ERROR), PHP_EOL;
