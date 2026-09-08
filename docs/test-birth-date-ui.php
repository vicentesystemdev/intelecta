<?php

// Optional manual UI fixture. Always isolated from PostgreSQL; refuses to overwrite its SQLite file.
// Setup: php docs/test-birth-date-ui.php
// Serve: php -S 127.0.0.1:8017 -t public docs/test-birth-date-ui.php
// Log in using the existing RolesAndUsersSeeder demo administrator. Never deploy this helper.
use App\Domains\Postulantes\Models\Postulante;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

if (! in_array(PHP_SAPI, ['cli', 'cli-server'], true)) {
    http_response_code(404);
    exit;
}
$root = dirname(__DIR__);
$database = $root.'/storage/framework/testing/postulante-birth-date-ui.sqlite';
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $asset = realpath($root.'/public'.$path);
    if ($path !== '/' && $asset && str_starts_with($asset, realpath($root.'/public').DIRECTORY_SEPARATOR) && is_file($asset)) {
        return false;
    }
} else {
    if (file_exists($database)) {
        throw new RuntimeException('La base UI ya existe; no se sobrescribirá.');
    }
    if (! is_dir(dirname($database))) {
        mkdir(dirname($database), 0777, true);
    }
    touch($database);
}
foreach ([
    'APP_ENV' => 'testing', 'APP_URL' => 'http://127.0.0.1:8017',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database, 'DB_URL' => '',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'file', 'SESSION_COOKIE' => 'intelecta_birth_date_ui',
    'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array', 'BCRYPT_ROUNDS' => '4',
    'TELESCOPE_ENABLED' => 'false', 'PULSE_ENABLED' => 'false', 'NIGHTWATCH_ENABLED' => 'false',
    'APP_CONFIG_CACHE' => $root.'/storage/framework/testing/dob-ui-config-unused.php',
] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}
define('LARAVEL_START', microtime(true));
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Se requiere la conexión SQLite aislada.');
}
if (PHP_SAPI === 'cli-server') {
    $app->handleRequest(Request::capture());
} else {
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--class' => RolesAndUsersSeeder::class, '--force' => true]);
    Postulante::create([
        'nombres_post' => 'Legacy Prueba', 'apellidos_post' => 'Calendario',
        'edad_post' => 22, 'fecha_nacimiento_post' => null,
        'gestion_post' => now()->year, 'estado_post' => 'activo',
    ]);
    echo json_encode(['isolated_sqlite' => true, 'legacy_fixture_id' => 1, 'postgres_modified' => false]), PHP_EOL;
}
