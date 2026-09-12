<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\TestCase;

#[Group('postgres-concurrency')]
class MatriculaCodigoConcurrencyPostgresTest extends TestCase
{
    private const LOCK_KEY = 820_008;

    private const TRIGGER = 'block8_matricula_insert_pause';

    private const FUNCTION = 'block8_matricula_insert_pause_fn';

    public function test_concurrent_matriculas_receive_distinct_stable_codes(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || env('RUN_POSTGRES_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Requiere PostgreSQL aislado y RUN_POSTGRES_CONCURRENCY=1.');
        }
        $this->assertStringContainsString('isolated', DB::connection()->getDatabaseName());
        $suffix = strtolower(str()->random(8));
        $programa = ProgramaAcademico::create(['nombre_prog' => "Matrícula {$suffix}", 'codigo_prog' => "MC-{$suffix}", 'estado_prog' => 'activo']);
        $grupo = GrupoAcademico::create(['id_prog' => $programa->id_prog, 'nombre_grupo' => "Grupo {$suffix}", 'codigo_grupo' => "MG-{$suffix}", 'capacidad_grupo' => 20, 'estado_grupo' => 'activo']);
        $postulantes = collect([1, 2])->map(fn (int $index) => Postulante::factory()->create(['email_post' => "matricula-{$index}-{$suffix}@intelecta.test", 'estado_post' => 'activo']));
        $inscripciones = $postulantes->map(fn (Postulante $postulante) => InscripcionAcademica::create(['id_prog' => $programa->id_prog, 'id_grupo' => $grupo->id_grupo, 'id_post' => $postulante->id_post, 'fecha_inscripcion' => now()->toDateString(), 'estado_inscripcion' => 'activo']));
        $workers = [];

        try {
            $this->installPauseTrigger();
            DB::select('SELECT pg_advisory_lock(?)', [self::LOCK_KEY]);
            foreach ($inscripciones as $inscripcion) {
                $workers[] = $this->matriculaProcess($inscripcion);
                $workers[array_key_last($workers)]->start();
                if (count($workers) === 1) {
                    $this->waitUntilInsertIsPaused();
                }
            }
            usleep(250_000);
            $this->assertTrue($workers[1]->isRunning(), 'La segunda matrícula debía coexistir con la primera operación bloqueada.');
            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput());
            }

            $codes = MatriculaAcademica::query()->whereIn('id_insc', $inscripciones->pluck('id_insc'))->pluck('codigo_mat')->all();
            $this->assertCount(2, array_unique($codes));
            foreach ($codes as $code) {
                $this->assertMatchesRegularExpression('/^MTR-\d{4}-\d{6}$/', $code);
            }
        } finally {
            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            foreach ($workers as $worker) {
                $worker->stop();
            }
            $this->removePauseTrigger();
            DB::table('habilitaciones_academicas')->whereIn('id_insc', $inscripciones->pluck('id_insc'))->delete();
            MatriculaAcademica::query()->whereIn('id_insc', $inscripciones->pluck('id_insc'))->forceDelete();
            InscripcionAcademica::query()->whereIn('id_insc', $inscripciones->pluck('id_insc'))->forceDelete();
            foreach ($postulantes as $postulante) {
                $postulante->forceDelete();
            }
            $grupo->forceDelete();
            $programa->forceDelete();
        }
    }

    private function matriculaProcess(InscripcionAcademica $inscripcion): Process
    {
        $script = $this->bootstrapScript().sprintf(<<<'PHP'
            $data = App\Domains\Academico\DTOs\MatriculaAcademicaData::fromArray([
                'id_insc' => %d,
                'monto_matricula_mat' => 100,
                'estado_matricula_mat' => 'activa',
            ]);
            $matricula = app(App\Domains\Academico\Services\MatriculaCuotaService::class)->saveMatricula($data);
            echo $matricula->codigo_mat;
            PHP,
            $inscripcion->id_insc,
        );

        return (new Process([PHP_BINARY, '-r', $script], base_path()))->setTimeout(20);
    }

    private function installPauseTrigger(): void
    {
        $this->removePauseTrigger();
        DB::unprepared(sprintf('CREATE FUNCTION %s() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN PERFORM pg_advisory_xact_lock(%d); RETURN NEW; END $$', self::FUNCTION, self::LOCK_KEY));
        DB::unprepared(sprintf('CREATE TRIGGER %s BEFORE INSERT ON matriculas_academicas FOR EACH ROW EXECUTE FUNCTION %s()', self::TRIGGER, self::FUNCTION));
    }

    private function removePauseTrigger(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        DB::unprepared(sprintf('DROP TRIGGER IF EXISTS %s ON matriculas_academicas', self::TRIGGER));
        DB::unprepared(sprintf('DROP FUNCTION IF EXISTS %s()', self::FUNCTION));
    }

    private function waitUntilInsertIsPaused(): void
    {
        $deadline = microtime(true) + 10;
        do {
            if ((int) DB::scalar("SELECT COUNT(*) FROM pg_locks WHERE locktype = 'advisory' AND classid = 0 AND objid = ? AND NOT granted", [self::LOCK_KEY]) > 0) {
                return;
            }
            usleep(50_000);
        } while (microtime(true) < $deadline);
        $this->fail('La primera matrícula no alcanzó la barrera concurrente PostgreSQL.');
    }

    private function bootstrapScript(): string
    {
        $autoload = var_export(base_path('vendor/autoload.php'), true);
        $bootstrap = var_export(base_path('bootstrap/app.php'), true);

        return "require {$autoload}; \$app = require {$bootstrap}; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); ";
    }
}
