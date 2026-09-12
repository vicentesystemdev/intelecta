<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\TestCase;

#[Group('postgres-concurrency')]
class InscripcionCapacityConcurrencyPostgresTest extends TestCase
{
    private const LOCK_KEY = 720_007;

    private const TRIGGER = 'block7_enrollment_insert_pause';

    private const FUNCTION = 'block7_enrollment_insert_pause_fn';

    public function test_two_enrollments_cannot_consume_the_same_last_seat(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || env('RUN_POSTGRES_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Requiere PostgreSQL aislado y RUN_POSTGRES_CONCURRENCY=1.');
        }

        $database = DB::connection()->getDatabaseName();
        $this->assertStringContainsString('isolated', $database);
        $suffix = strtolower(str()->random(10));
        $programa = ProgramaAcademico::create([
            'nombre_prog' => "Programa cupo {$suffix}",
            'codigo_prog' => "CUP-{$suffix}",
            'estado_prog' => 'activo',
        ]);
        $grupo = GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => "Grupo cupo {$suffix}",
            'codigo_grupo' => "G-{$suffix}",
            'capacidad_grupo' => 1,
            'estado_grupo' => 'activo',
        ]);
        $postulantes = [
            Postulante::factory()->create(['email_post' => "cupo-a-{$suffix}@intelecta.test"]),
            Postulante::factory()->create(['email_post' => "cupo-b-{$suffix}@intelecta.test"]),
        ];
        $workers = [];

        try {
            $this->installPauseTrigger();
            DB::select('SELECT pg_advisory_lock(?)', [self::LOCK_KEY]);

            $workers[] = $this->enrollmentProcess($programa, $grupo, $postulantes[0]);
            $workers[0]->start();
            $this->waitUntilFirstInsertIsPaused();

            $workers[] = $this->enrollmentProcess($programa, $grupo, $postulantes[1]);
            $workers[1]->start();
            usleep(250_000);
            $this->assertTrue($workers[1]->isRunning(), 'La segunda inscripción debía esperar el lock del grupo.');

            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput());
            }

            $outputs = array_map(fn (Process $worker) => trim($worker->getOutput()), $workers);
            sort($outputs);
            $this->assertSame(['CREATED', 'FULL'], $outputs);
            $this->assertSame(1, InscripcionAcademica::query()
                ->where('id_grupo', $grupo->id_grupo)
                ->where('estado_inscripcion', 'activo')
                ->count());
        } finally {
            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            foreach ($workers as $worker) {
                $worker->stop();
            }
            $this->removePauseTrigger();
            InscripcionAcademica::query()->where('id_grupo', $grupo->id_grupo)->delete();
            foreach ($postulantes as $postulante) {
                $postulante->forceDelete();
            }
            $grupo->forceDelete();
            $programa->forceDelete();
        }
    }

    private function enrollmentProcess(
        ProgramaAcademico $programa,
        GrupoAcademico $grupo,
        Postulante $postulante,
    ): Process {
        $script = $this->bootstrapScript().sprintf(<<<'PHP'
            $data = App\Domains\Academico\DTOs\InscripcionAcademicaData::fromArray([
                'id_prog' => %d,
                'id_grupo' => %d,
                'id_post' => %d,
                'fecha_inscripcion' => now()->toDateString(),
                'estado_inscripcion' => 'activo',
            ]);
            try {
                app(App\Domains\Academico\Services\AcademicoService::class)->saveInscripcion($data);
                echo 'CREATED';
            } catch (Illuminate\Validation\ValidationException $exception) {
                if (! isset($exception->errors()['id_grupo'])) {
                    throw $exception;
                }
                echo 'FULL';
            }
            PHP,
            $programa->id_prog,
            $grupo->id_grupo,
            $postulante->id_post,
        );

        return (new Process([PHP_BINARY, '-r', $script], base_path()))->setTimeout(20);
    }

    private function installPauseTrigger(): void
    {
        $this->removePauseTrigger();
        DB::unprepared(sprintf(
            'CREATE FUNCTION %s() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN PERFORM pg_advisory_xact_lock(%d); RETURN NEW; END $$',
            self::FUNCTION,
            self::LOCK_KEY,
        ));
        DB::unprepared(sprintf(
            'CREATE TRIGGER %s BEFORE INSERT ON inscripciones_academicas FOR EACH ROW EXECUTE FUNCTION %s()',
            self::TRIGGER,
            self::FUNCTION,
        ));
    }

    private function removePauseTrigger(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(sprintf('DROP TRIGGER IF EXISTS %s ON inscripciones_academicas', self::TRIGGER));
        DB::unprepared(sprintf('DROP FUNCTION IF EXISTS %s()', self::FUNCTION));
    }

    private function waitUntilFirstInsertIsPaused(): void
    {
        $deadline = microtime(true) + 10;
        do {
            $waiting = (int) DB::scalar(
                "SELECT COUNT(*) FROM pg_locks WHERE locktype = 'advisory' AND classid = 0 AND objid = ? AND NOT granted",
                [self::LOCK_KEY],
            );
            if ($waiting > 0) {
                return;
            }
            usleep(50_000);
        } while (microtime(true) < $deadline);

        $this->fail('La primera inscripción no alcanzó la barrera concurrente PostgreSQL.');
    }

    private function bootstrapScript(): string
    {
        $autoload = var_export(base_path('vendor/autoload.php'), true);
        $bootstrap = var_export(base_path('bootstrap/app.php'), true);

        return "require {$autoload}; \$app = require {$bootstrap}; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); ";
    }
}
