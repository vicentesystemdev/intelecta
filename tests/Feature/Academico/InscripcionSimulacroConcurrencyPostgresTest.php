<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Process\Process;
use Tests\TestCase;

#[Group('postgres-concurrency')]
class InscripcionSimulacroConcurrencyPostgresTest extends TestCase
{
    private const LOCK_KEY = 720_071;

    public function test_enrollment_update_and_simulation_start_share_a_deadlock_free_lock_order(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || env('RUN_POSTGRES_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Requiere PostgreSQL aislado y RUN_POSTGRES_CONCURRENCY=1.');
        }

        $this->assertStringContainsString('isolated', DB::connection()->getDatabaseName());
        $suffix = strtolower(str()->random(10));
        $programa = ProgramaAcademico::create([
            'nombre_prog' => "Programa lock {$suffix}",
            'codigo_prog' => "LP-{$suffix}",
            'estado_prog' => 'activo',
        ]);
        $grupo = GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => "Grupo lock {$suffix}",
            'codigo_grupo' => "LG-{$suffix}",
            'capacidad_grupo' => 2,
            'estado_grupo' => 'activo',
        ]);
        $postulante = Postulante::factory()->create([
            'email_post' => "lock-{$suffix}@intelecta.test",
        ]);
        $inscripcion = InscripcionAcademica::create([
            'id_prog' => $programa->id_prog,
            'id_grupo' => $grupo->id_grupo,
            'id_post' => $postulante->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);
        $pregunta = Pregunta::create([
            'enunciado_preg' => "Pregunta lock {$suffix}",
            'tipo_preg' => 'respuesta_corta',
            'puntaje_preg' => 1,
            'estado_preg' => 'activo',
        ]);
        $plantilla = PlantillaEvaluacion::create([
            'nombre_plan' => "Plantilla lock {$suffix}",
            'estado_plan' => 'activa',
        ]);
        $plantilla->preguntas()->attach($pregunta->id_preg, [
            'orden_pp' => 1,
            'puntaje_pp' => 100,
        ]);
        $simulacro = SimulacroProgramado::create([
            'id_prog' => $programa->id_prog,
            'id_grupo' => $grupo->id_grupo,
            'id_plantilla' => $plantilla->id_plan,
            'titulo_sim' => "Simulacro lock {$suffix}",
            'estado_sim' => 'programado',
        ]);
        $evaluacionWorker = $this->evaluationProcess($postulante, $plantilla, $simulacro);
        $enrollmentWorker = $this->enrollmentProcess($postulante, $programa, $grupo, $inscripcion);

        try {
            DB::select('SELECT pg_advisory_lock(?)', [self::LOCK_KEY]);
            $evaluacionWorker->start();
            $this->waitUntilEvaluationLockedPostulante();

            $enrollmentWorker->start();
            $this->waitUntilEnrollmentWaitsForGroup();

            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            $evaluacionWorker->wait();
            $enrollmentWorker->wait();

            $this->assertTrue($evaluacionWorker->isSuccessful(), $evaluacionWorker->getErrorOutput());
            $this->assertTrue($enrollmentWorker->isSuccessful(), $enrollmentWorker->getErrorOutput());
            $this->assertSame('EVALUATION_STARTED', trim($evaluacionWorker->getOutput()));
            $this->assertSame('ENROLLMENT_UPDATED', trim($enrollmentWorker->getOutput()));
            $this->assertSame(1, EvaluacionAplicada::where('id_sim', $simulacro->id_sim)->count());
            $this->assertDatabaseHas('inscripciones_academicas', [
                'id_insc' => $inscripcion->id_insc,
                'id_prog' => $programa->id_prog,
                'id_grupo' => $grupo->id_grupo,
                'id_post' => $postulante->id_post,
                'estado_inscripcion' => 'activo',
            ]);
        } finally {
            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            $evaluacionWorker->stop();
            $enrollmentWorker->stop();
            EvaluacionAplicada::withTrashed()->where('id_sim', $simulacro->id_sim)->forceDelete();
            $simulacro->forceDelete();
            $inscripcion->delete();
            DB::table('plantilla_preguntas')->where('id_plan', $plantilla->id_plan)->delete();
            $plantilla->forceDelete();
            $pregunta->forceDelete();
            $postulante->forceDelete();
            $grupo->forceDelete();
            $programa->forceDelete();
        }
    }

    private function evaluationProcess(
        Postulante $postulante,
        PlantillaEvaluacion $plantilla,
        SimulacroProgramado $simulacro,
    ): Process {
        $script = $this->bootstrapScript().sprintf(<<<'PHP'
            Illuminate\Support\Facades\DB::statement("SET application_name = 'block7_evaluation_worker'");
            Illuminate\Support\Facades\DB::statement("SET deadlock_timeout = '100ms'");
            Illuminate\Support\Facades\DB::listen(function ($event) {
                static $paused = false;
                if (! $paused && str_contains($event->sql, '"postulantes"') && str_contains($event->sql, 'for update')) {
                    $paused = true;
                    Illuminate\Support\Facades\DB::select('SELECT pg_advisory_xact_lock(%d)');
                }
            });
            app(App\Domains\Resultados\Services\EvaluacionAplicadaService::class)->iniciar(
                App\Domains\Postulantes\Models\Postulante::findOrFail(%d),
                App\Domains\Evaluaciones\Models\PlantillaEvaluacion::findOrFail(%d),
                %d,
            );
            echo 'EVALUATION_STARTED';
            PHP,
            self::LOCK_KEY,
            $postulante->id_post,
            $plantilla->id_plan,
            $simulacro->id_sim,
        );

        return (new Process([PHP_BINARY, '-r', $script], base_path()))->setTimeout(20);
    }

    private function enrollmentProcess(
        Postulante $postulante,
        ProgramaAcademico $programa,
        GrupoAcademico $grupo,
        InscripcionAcademica $inscripcion,
    ): Process {
        $script = $this->bootstrapScript().sprintf(<<<'PHP'
            Illuminate\Support\Facades\DB::statement("SET application_name = 'block7_enrollment_worker'");
            Illuminate\Support\Facades\DB::statement("SET deadlock_timeout = '100ms'");
            $data = App\Domains\Academico\DTOs\InscripcionAcademicaData::fromArray([
                'id_post' => %d,
                'id_prog' => %d,
                'id_grupo' => %d,
                'fecha_inscripcion' => now()->toDateString(),
                'estado_inscripcion' => 'activo',
            ]);
            app(App\Domains\Academico\Services\AcademicoService::class)->saveInscripcion(
                $data,
                App\Domains\Academico\Models\InscripcionAcademica::findOrFail(%d),
            );
            echo 'ENROLLMENT_UPDATED';
            PHP,
            $postulante->id_post,
            $programa->id_prog,
            $grupo->id_grupo,
            $inscripcion->id_insc,
        );

        return (new Process([PHP_BINARY, '-r', $script], base_path()))->setTimeout(20);
    }

    private function waitUntilEvaluationLockedPostulante(): void
    {
        $this->waitUntil(
            "SELECT COUNT(*) FROM pg_locks WHERE locktype = 'advisory' AND classid = 0 AND objid = ? AND NOT granted",
            [self::LOCK_KEY],
            'La evaluación no alcanzó la barrera posterior al lock de Postulante.',
        );
    }

    private function waitUntilEnrollmentWaitsForGroup(): void
    {
        $this->waitUntil(
            "SELECT COUNT(*) FROM pg_stat_activity WHERE application_name = 'block7_enrollment_worker' AND wait_event_type = 'Lock' AND query LIKE '%grupos_academicos%'",
            [],
            'La inscripción no quedó esperando el lock del Grupo.',
        );
    }

    private function waitUntil(string $sql, array $bindings, string $failure): void
    {
        $deadline = microtime(true) + 10;
        do {
            if ((int) DB::scalar($sql, $bindings) > 0) {
                return;
            }
            usleep(50_000);
        } while (microtime(true) < $deadline);

        $this->fail($failure);
    }

    private function bootstrapScript(): string
    {
        $autoload = var_export(base_path('vendor/autoload.php'), true);
        $bootstrap = var_export(base_path('bootstrap/app.php'), true);

        return "require {$autoload}; \$app = require {$bootstrap}; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); ";
    }
}
