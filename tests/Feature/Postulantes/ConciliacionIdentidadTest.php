<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\AuditoriaIdentidadService;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Models\User;
use Database\Seeders\BaseLimpiaAvalanchaSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConciliacionIdentidadTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'Estudiante'): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }

    private function manifest(int $count = 2): array
    {
        $service = app(ConciliacionIdentidadService::class);
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $user = $this->user();
            $post = Postulante::factory()->create();
            $rows[] = ['user_id' => $user->id, 'id_post' => $post->id_post, 'clasificacion' => 'A',
                'revision' => 'aprobado', 'evidencia' => 'Acta documental explícita de prueba, no inferida por correo.',
                'huella_usuario' => $service->huella($user->fresh()), 'huella_expediente' => $service->huella($post->fresh())];
        }

        return ['destino' => $service->destino(), 'filas' => $rows];
    }

    public function test_default_dry_run_has_no_database_writes_or_actor_attribution(): void
    {
        $manifest = $this->manifest();
        $before = $this->snapshot();
        $rows = app(ConciliacionIdentidadService::class)->ejecutar($manifest);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame(['propuesto_sin_escritura', 'propuesto_sin_escritura'], array_column($rows, 'resultado'));
    }

    public function test_apply_reuses_audit_and_a_second_run_does_not_overwrite_anything(): void
    {
        $manifest = $this->manifest();
        $actor = $this->user('Super Administrador');
        $service = app(ConciliacionIdentidadService::class);
        $service->ejecutar($manifest, true, $actor, 'Acta explícita de conciliación de prueba');
        $this->assertSame(2, Postulante::whereNotNull('user_id')->count());
        $this->assertSame(2, DB::table('bitacora_sistema')->where('accion', 'vincular_identidad')->where('user_id', $actor->id)->count());
        $before = $this->snapshot();
        $result = $service->ejecutar($manifest, true, $actor, 'Reejecución autorizada del acta de prueba');
        $this->assertSame($before, $this->snapshot());
        $this->assertSame(['ya_vinculado_sin_cambios', 'ya_vinculado_sin_cambios'], array_column($result, 'resultado'));
    }

    public function test_late_conflict_rolls_back_earlier_rows_and_audits(): void
    {
        $manifest = $this->manifest();
        $actor = $this->user('Super Administrador');
        Postulante::findOrFail($manifest['filas'][1]['id_post'])->user()->associate($this->user())->save();
        $before = $this->snapshot();
        try {
            app(ConciliacionIdentidadService::class)->ejecutar($manifest, true, $actor, 'Acta de prueba con conflicto al final');
            $this->fail('El conflicto debe abortar todo el lote.');
        } catch (ValidationException) {
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function test_silent_audit_failure_rolls_back_the_link(): void
    {
        $manifest = $this->manifest(1);
        $actor = $this->user('Super Administrador');
        $mock = Mockery::mock(BitacoraService::class);
        $mock->shouldReceive('registrar')->once()->andReturnNull();
        $this->app->instance(BitacoraService::class, $mock);
        $before = $this->snapshot();
        try {
            app(ConciliacionIdentidadService::class)->ejecutar($manifest, true, $actor, 'Acta de prueba con bitácora no disponible');
            $this->fail('Una bitácora no persistida debe abortar.');
        } catch (RuntimeException) {
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function test_apply_requires_explicit_authorized_actor(): void
    {
        $manifest = $this->manifest();
        $this->expectException(AuthorizationException::class);
        app(ConciliacionIdentidadService::class)->ejecutar($manifest, true, $this->user('Administrador'), 'Acta no autorizada de prueba');
    }

    public function test_ambiguous_or_unreviewed_manifest_cannot_be_applied(): void
    {
        $manifest = $this->manifest(1);
        $actor = $this->user('Super Administrador');
        foreach ([['clasificacion', 'B'], ['clasificacion', 'E'], ['revision', 'pendiente'], ['huella_usuario', str_repeat('0', 64)]] as [$field, $value]) {
            $changed = $manifest;
            $changed['filas'][0][$field] = $value;
            try {
                app(ConciliacionIdentidadService::class)->ejecutar($changed, true, $actor, 'Acta de revisión explícita de prueba');
                $this->fail('No se debe aplicar una fila ambigua, pendiente o cambiada.');
            } catch (ValidationException) {
                $this->assertSame(0, Postulante::whereNotNull('user_id')->count());
            }
        }
    }

    public function test_duplicates_wrong_destination_and_archived_profiles_fail_closed(): void
    {
        $manifest = $this->manifest(1);
        $variants = [$manifest, $manifest, $manifest];
        $variants[0]['filas'][] = $manifest['filas'][0];
        $variants[1]['destino']['database'] = 'otra_base';
        Postulante::findOrFail($manifest['filas'][0]['id_post'])->delete();
        foreach ($variants as $variant) {
            try {
                app(ConciliacionIdentidadService::class)->ejecutar($variant);
                $this->fail('Se debe rechazar duplicidad, destino incorrecto o archivo.');
            } catch (ValidationException) {
                $this->assertSame(0, Postulante::withTrashed()->whereNotNull('user_id')->count());
            }
        }
    }

    public function test_name_or_email_alone_never_generates_an_approved_pair(): void
    {
        $user = $this->user();
        Postulante::factory()->create(['email_post' => $user->email]);
        $report = app(AuditoriaIdentidadService::class)->generar();
        $this->assertSame('B', $report['filas'][0]['clasificacion']);
        Postulante::factory()->create(['email_post' => $user->email]);
        $report = app(AuditoriaIdentidadService::class)->generar();
        $this->assertSame(['E', 'E'], array_column($report['filas'], 'clasificacion'));
        $this->assertSame(0, Postulante::whereNotNull('user_id')->count());
    }

    public function test_confirmed_unique_links_are_not_conflicts_just_because_students_are_namesakes(): void
    {
        foreach ([8400001, 8400002] as $ci) {
            $user = $this->user();
            $user->name = 'Persona Prueba Académica';
            $user->save();
            Postulante::factory()->withUser($user)->create(['ci_post' => (string) $ci, 'email_post' => $user->email]);
        }
        $report = app(AuditoriaIdentidadService::class)->generar();
        $this->assertSame(['A', 'A'], array_column($report['filas'], 'clasificacion'));
        $this->assertSame(2, $report['resumen']['vinculos_actuales']);
        $this->assertSame(0, $report['resumen']['cuentas_sin_vinculo']);
    }

    public function test_all_72_demo_profiles_and_every_existing_student_account_are_audited(): void
    {
        // Exclusivamente RefreshDatabase (:memory: o PostgreSQL aislado). Nunca se ejecuta en intelecta.
        $this->seed(BaseLimpiaAvalanchaSeeder::class);
        $fixturePassword = 'Prueba!Aa-'.Str::random(24);
        foreach (User::role('Estudiante')->get() as $student) {
            $student->password = $fixturePassword;
            $student->save();
        }
        foreach (Postulante::whereNotNull('user_id')->get() as $post) {
            $post->user()->dissociate()->save();
        }
        $audit = app(AuditoriaIdentidadService::class);
        $report = $audit->generar();
        $this->assertSame(72, count($report['filas']));
        $this->assertSame(72, $report['resumen']['coinciden_fixture']);
        $this->assertSame(['A' => 3, 'D' => 69], $report['resumen']['clasificaciones']);
        $this->assertSame([403, 403, 403], array_column($report['cuentas'], 'middleware_identidad_http'));
        $rows = array_values(array_filter($report['filas'], fn ($row) => $row['clasificacion'] === 'A'));
        foreach ($rows as &$row) {
            $row['revision'] = 'aprobado';
        }
        unset($row);
        $manifest = ['destino' => $report['destino'], 'filas' => $rows];
        $actor = User::role('Super Administrador')->firstOrFail();
        $financial = $this->financialSnapshot();
        app(ConciliacionIdentidadService::class)->ejecutar($manifest, true, $actor, 'Autorización DEMO exclusivamente en prueba aislada');
        $after = $audit->generar();
        $this->assertSame([200, 200, 200], array_column($after['cuentas'], 'middleware_identidad_http'));
        $template = PlantillaEvaluacion::where('estado_plan', 'activa')->firstOrFail();
        foreach (User::role('Estudiante')->orderBy('id')->get() as $user) {
            $post = $user->postulante;
            $allowed = app(AccesoEvaluacionService::class)->evaluar($post)['permitido'];
            $this->post('/logout');
            $this->post('/login', ['email' => $user->email, 'password' => $fixturePassword])->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->get('/estudiante/mi-ficha')->assertOk();
            $this->get('/estudiante/evaluaciones')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/Evaluaciones')->where('postulanteVinculado', true)->where('accesoEvaluaciones.permitido', $allowed));
            $response = $this->postJson('/estudiante/evaluaciones/'.$template->id_plan.'/iniciar');
            $allowed ? $response->assertRedirect() : $response->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        }
        $this->assertSame($financial, $this->financialSnapshot());
        foreach (Postulante::all() as $post) {
            $this->assertSame($post->estado_post === 'activo' && $post->habilitacionesAcademicas->last()->habilitado_evaluaciones_hab,
                app(AccesoEvaluacionService::class)->evaluar($post)['permitido']);
        }
        $this->assertSame(69, $after['resumen']['expedientes_sin_cuenta_asignada']);
    }

    private function snapshot(): array
    {
        return collect(['users', 'postulantes', 'model_has_roles', 'bitacora_sistema'])->mapWithKeys(fn ($table) => [$table => hash('sha256', DB::table($table)->get()->toJson())])->all();
    }

    private function financialSnapshot(): array
    {
        return collect(['inscripciones_academicas', 'matriculas_academicas', 'habilitaciones_academicas', 'cuotas_academicas', 'model_has_roles', 'role_has_permissions'])
            ->mapWithKeys(fn ($table) => [$table => hash('sha256', DB::table($table)->get()->toJson())])->all();
    }
}
