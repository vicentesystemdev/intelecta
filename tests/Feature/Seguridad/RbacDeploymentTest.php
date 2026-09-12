<?php

namespace Tests\Feature\Seguridad;

use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Services\DesplegarMatrizRbac;
use App\Domains\Seguridad\Support\MatrizRbac;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacDeploymentTest extends TestCase
{
    use RefreshDatabase;

    private string $snapshot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshot = sys_get_temp_dir().'/intelecta-rbac-'.bin2hex(random_bytes(12)).'.json';
        $old = array_diff(MatrizRbac::CATALOG, [
            'usuarios.asignar_roles',
            'materias.crear',
            'materias.editar',
            'materias.cambiar_estado',
        ]);
        foreach ($old as $name) {
            Permission::findOrCreate($name, 'web');
        }
        foreach (MatrizRbac::ROLES as $name) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions(match ($name) {
                'Super Administrador', 'Administrador' => $old,
                'Docente' => [...MatrizRbac::TEACHER, 'dashboard.ver', 'programas.ver', 'grupos.ver', 'postulantes.ver', 'ficha-academica.ver', 'ranking.ver', 'asistencia.ver', 'simulacros.ver', 'resultados.ver', 'reportes.ver', 'indicadores.ver', 'learning_analytics.ver', 'evaluaciones.ver'],
                default => [],
            });
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->snapshot) && is_file($this->snapshot)) {
            unlink($this->snapshot); // Only this test's explicit random snapshot, never a directory.
        }
        parent::tearDown();
    }

    public function test_snapshot_apply_idempotence_rollback_and_reapply_preserve_accounts_profiles_and_sessions(): void
    {
        $admin = User::factory()->active()->create()->assignRole('Administrador');
        $teacher = User::factory()->active()->create()->assignRole('Docente');
        $person = PersonalInstitucional::factory()->pending()->create(['user_id' => $teacher->id]);
        TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        $student = User::factory()->active()->create()->assignRole('Estudiante');
        Postulante::factory()->create(); // Main-like legacy student intentionally unlinked.
        DB::table('sessions')->insert(['id' => 'rbac-preserved', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => 1]);
        $tables = ['users', 'model_has_roles', 'model_has_permissions', 'personal_institucional', 'tutores_academicos', 'postulantes', 'cargos', 'sessions', 'bitacora_sistema'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->get()->toJson();
        }
        $service = app(DesplegarMatrizRbac::class);
        $state = $service->state();
        $this->assertDatabaseCount('role_has_permissions', 180);
        $this->assertTrue($service->apply($this->snapshot));
        $this->assertSame($state, json_decode(file_get_contents($this->snapshot), true)['before']);
        $this->assertSame($service->target(), $service->matrix($service->state()));
        $this->assertDatabaseCount('role_has_permissions', 146);
        $this->assertDatabaseCount('permissions', 84);
        $this->assertFalse($service->apply($this->snapshot));
        $this->assertTrue($service->restore($this->snapshot));
        $this->assertSame($state, $service->state());
        $this->assertFalse($service->restore($this->snapshot));
        $second = $this->snapshot.'.second';
        try {
            $this->assertTrue($service->apply($second));
            foreach ($tables as $table) {
                $this->assertSame($before[$table], DB::table($table)->get()->toJson(), $table);
            }
            $this->assertNull($admin->fresh()->personalInstitucional);
            $this->assertNull($student->fresh()->postulante);
            $this->assertTrue($teacher->fresh()->can('postulantes.ver'));
            $this->assertTrue($teacher->fresh()->can('asistencia.crear'));
            $this->assertFalse($teacher->fresh()->can('asistencia.editar'));
            $this->assertTrue($teacher->fresh()->can('preguntas.ver'));
            $this->assertFalse($admin->fresh()->can('usuarios.crear'));
        } finally {
            if (is_file($second)) {
                unlink($second);
            }
        }
    }

    public function test_snapshot_failure_cancels_all_writes(): void
    {
        $service = app(DesplegarMatrizRbac::class);
        $before = $service->state();
        try {
            $service->apply($this->snapshot.'/missing-parent.json');
            $this->fail('Debe fallar sin snapshot.');
        } catch (RuntimeException) {
            $this->assertSame($before, $service->state());
        }
    }

    public function test_rollback_refuses_later_manual_matrix_changes(): void
    {
        $service = app(DesplegarMatrizRbac::class);
        $service->apply($this->snapshot);
        Role::findByName('Docente')->syncPermissions(['preguntas.ver']);
        $before = $service->state();
        try {
            $service->restore($this->snapshot);
            $this->fail('No debe sobrescribir cambios posteriores.');
        } catch (RuntimeException) {
            $this->assertSame($before, $service->state());
        }
    }

    public function test_deployment_rejects_unreviewed_direct_permissions(): void
    {
        User::factory()->active()->create()->givePermissionTo('preguntas.ver');
        $this->expectException(RuntimeException::class);
        app(DesplegarMatrizRbac::class)->apply($this->snapshot);
    }

    public function test_command_defaults_to_read_only_and_requires_explicit_database_and_snapshot(): void
    {
        $service = app(DesplegarMatrizRbac::class);
        $before = $service->state();
        $this->artisan('rbac:matriz')->assertSuccessful();
        $this->artisan('rbac:matriz', ['--apply' => true])->assertFailed();
        $this->artisan('rbac:matriz', ['--apply' => true, '--confirm-database' => DB::connection()->getDatabaseName()])->assertFailed();
        $this->assertSame($before, $service->state());
    }
}
