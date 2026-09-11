<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Process\Process;
use Tests\TestCase;

#[Group('postgres-concurrency')]
class AsistenciaConcurrencyPostgresTest extends TestCase
{
    private const LOCK_KEY = 620_006;

    private const TRIGGER = 'block6_assignment_update_pause';

    private const FUNCTION = 'block6_assignment_update_pause_fn';

    public function test_assignment_revocation_and_attendance_creation_are_serialized(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || env('RUN_POSTGRES_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Requiere PostgreSQL aislado y RUN_POSTGRES_CONCURRENCY=1.');
        }
        $database = DB::connection()->getDatabaseName();
        $this->assertStringContainsString('isolated', $database);

        $permission = Permission::findOrCreate('asistencia.crear', 'web');
        Role::findOrCreate('Docente', 'web')->givePermissionTo($permission);

        $suffix = strtolower(str()->random(10));
        $teacher = User::factory()->active()->create([
            'email' => "concurrencia-{$suffix}@intelecta.test",
        ])->assignRole('Docente');
        $personal = PersonalInstitucional::factory()->pending()->create(['user_id' => $teacher->id]);
        $tutor = TutorAcademico::factory()->create(['personal_id' => $personal->id_personal]);
        $program = ProgramaAcademico::create([
            'nombre_prog' => "Programa concurrencia {$suffix}",
            'codigo_prog' => "CON-{$suffix}",
        ]);
        $group = GrupoAcademico::create([
            'id_prog' => $program->id_prog,
            'nombre_grupo' => "Grupo concurrencia {$suffix}",
            'codigo_grupo' => "GC-{$suffix}",
        ]);
        $student = Postulante::factory()->create(['email_post' => "estudiante-{$suffix}@intelecta.test"]);
        $enrollment = InscripcionAcademica::create([
            'id_prog' => $program->id_prog,
            'id_grupo' => $group->id_grupo,
            'id_post' => $student->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);
        $assignment = AsignacionTutor::create([
            'id_tutor' => $tutor->id_tutor,
            'id_prog' => $program->id_prog,
            'id_grupo' => $group->id_grupo,
            'estado_asig' => 'activo',
        ]);

        $revocation = null;
        $attendance = null;
        try {
            $this->installPauseTrigger();
            DB::select('SELECT pg_advisory_lock(?)', [self::LOCK_KEY]);

            $revocation = $this->revocationProcess($assignment);
            $revocation->start();
            $this->waitUntilRevocationIsPaused();

            $attendance = $this->attendanceProcess($teacher, $group, $student);
            $attendance->start();
            usleep(250_000);
            $this->assertTrue($attendance->isRunning(), 'La asistencia debía esperar el lock de la asignación.');

            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            $revocation->wait();
            $attendance->wait();

            $this->assertTrue($revocation->isSuccessful(), $revocation->getErrorOutput());
            $this->assertSame('REVOKED', trim($revocation->getOutput()));
            $this->assertTrue($attendance->isSuccessful(), $attendance->getErrorOutput());
            $this->assertSame('DENIED', trim($attendance->getOutput()));
            $this->assertSame('inactivo', $assignment->fresh()->estado_asig);
            $this->assertDatabaseMissing('asistencias_academicas', [
                'id_grupo' => $group->id_grupo,
                'id_post' => $student->id_post,
                'sesion_asist' => 'Concurrencia Bloque 6',
            ]);
        } finally {
            DB::select('SELECT pg_advisory_unlock(?)', [self::LOCK_KEY]);
            $revocation?->stop();
            $attendance?->stop();
            $this->removePauseTrigger();

            AsistenciaAcademica::withTrashed()->where('id_grupo', $group->id_grupo)->forceDelete();
            $assignment->forceDelete();
            $enrollment->delete();
            $student->forceDelete();
            $group->forceDelete();
            $program->forceDelete();
            $tutor->forceDelete();
            $personal->delete();
            $teacher->delete();
        }
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
            'CREATE TRIGGER %s BEFORE UPDATE ON asignaciones_tutores FOR EACH ROW EXECUTE FUNCTION %s()',
            self::TRIGGER,
            self::FUNCTION,
        ));
    }

    private function removePauseTrigger(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(sprintf('DROP TRIGGER IF EXISTS %s ON asignaciones_tutores', self::TRIGGER));
        DB::unprepared(sprintf('DROP FUNCTION IF EXISTS %s()', self::FUNCTION));
    }

    private function waitUntilRevocationIsPaused(): void
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

        $this->fail('La revocación no alcanzó el punto de sincronización PostgreSQL.');
    }

    private function revocationProcess(AsignacionTutor $assignment): Process
    {
        $script = $this->bootstrapScript().sprintf(<<<'PHP'
            $assignment = App\Domains\Academico\Models\AsignacionTutor::findOrFail(%d);
            $data = App\Domains\Academico\DTOs\AsignacionTutorData::fromArray([
                'id_tutor' => %d,
                'id_prog' => %d,
                'id_grupo' => %d,
                'estado_asig' => 'inactivo',
            ]);
            app(App\Domains\Academico\Services\AsignacionTutorService::class)->save($data, $assignment);
            echo 'REVOKED';
            PHP,
            $assignment->id_asig,
            $assignment->id_tutor,
            $assignment->id_prog,
            $assignment->id_grupo,
        );

        return $this->process($script);
    }

    private function attendanceProcess(User $teacher, GrupoAcademico $group, Postulante $student): Process
    {
        $script = $this->bootstrapScript().sprintf(<<<'PHP'
            $user = App\Models\User::findOrFail(%d);
            $data = App\Domains\Academico\DTOs\AsistenciaAcademicaData::fromArray([
                'id_grupo' => %d,
                'id_post' => %d,
                'fecha_asist' => now()->toDateString(),
                'sesion_asist' => 'Concurrencia Bloque 6',
                'estado_asist' => 'presente',
            ]);
            try {
                app(App\Domains\Academico\Services\AsistenciaAcademicaService::class)->save($data, $user);
                echo 'CREATED';
                exit(2);
            } catch (Illuminate\Auth\Access\AuthorizationException) {
                echo 'DENIED';
            }
            PHP,
            $teacher->id,
            $group->id_grupo,
            $student->id_post,
        );

        return $this->process($script);
    }

    private function bootstrapScript(): string
    {
        $autoload = var_export(base_path('vendor/autoload.php'), true);
        $bootstrap = var_export(base_path('bootstrap/app.php'), true);

        return "require {$autoload}; \$app = require {$bootstrap}; \$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); ";
    }

    private function process(string $script): Process
    {
        return (new Process([PHP_BINARY, '-r', $script], base_path()))->setTimeout(15);
    }
}
