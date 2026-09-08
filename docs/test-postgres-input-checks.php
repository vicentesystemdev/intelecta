<?php

// Integration checks use ONLY temporary tables and always roll back.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('Esta prueba requiere PostgreSQL.');
}

$migration = require __DIR__.'/../database/migrations/2026_09_07_000000_add_basic_input_checks.php';
$started = microtime(true);
$passed = 0;
DB::beginTransaction();
try {
    foreach ($migration->checks() as $table => [$primaryKey, $checks]) {
        foreach ($checks as $name => $condition) {
            DB::statement('CREATE TEMP TABLE intelecta_validation_probe ON COMMIT DROP AS SELECT * FROM "'.$table.'" WITH NO DATA');
            DB::statement('ALTER TABLE pg_temp.intelecta_validation_probe ADD CONSTRAINT "'.$name.'" CHECK ('.$condition.')');
            if (preg_match('/^(\w+) BETWEEN ([\d.]+) AND ([\d.]+)$/', $condition, $matches)) {
                [, $field, $minimum, $maximum] = $matches;
                $cases = [
                    [[$field => $minimum], true], [[$field => $maximum], true],
                    [[$field => ((float) $minimum) - 1], false],
                    [[$field => ((float) $maximum) + 1], false],
                    [[$field => null], true],
                ];
                // Column precision also limits the largest amounts/integers; test the CHECK without overflow.
                if ($maximum === '99999999.99' || $maximum === '32767') {
                    unset($cases[3]);
                }
            } else {
                preg_match('/^(\w+) (>=|>) (\w+)$/', $condition, $matches);
                [, $end, $operator, $start] = $matches;
                $time = str_starts_with($start, 'hora_');
                $first = $time ? '09:00' : '2026-09-07';
                $later = $time ? '10:00' : '2026-09-08';
                $earlier = $time ? '08:00' : '2026-09-06';
                $cases = [
                    [[$start => $first, $end => $later], true],
                    [[$start => $first, $end => $first], $operator === '>='],
                    [[$start => $first, $end => $earlier], false],
                    [[$start => null, $end => $later], true],
                ];
            }
            foreach ($cases as [$values, $expected]) {
                DB::statement('SAVEPOINT input_case');
                $accepted = true;
                try {
                    DB::table('pg_temp.intelecta_validation_probe')->insert($values);
                } catch (QueryException $exception) {
                    $accepted = false;
                    if (($exception->errorInfo[0] ?? null) !== '23514') {
                        throw $exception;
                    }
                } finally {
                    DB::statement('ROLLBACK TO SAVEPOINT input_case');
                    DB::statement('RELEASE SAVEPOINT input_case');
                }
                if ($accepted !== $expected) {
                    throw new RuntimeException('Falló el CHECK '.$name.' con '.json_encode($values));
                }
                $passed++;
            }
            DB::statement('DROP TABLE pg_temp.intelecta_validation_probe');
        }
    }
} finally {
    DB::rollBack();
}

echo json_encode(['checks' => 18, 'tests' => $passed, 'passed' => $passed, 'failed' => 0, 'assertions' => $passed, 'duration_ms' => round((microtime(true) - $started) * 1000), 'real_data_modified' => false]), PHP_EOL;
