<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Services\AuditoriaIdentidadService;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Prueba opt-in sobre una RESTAURACIÓN. Sin RefreshDatabase, migraciones ni seeders. */
class PoblacionEstudiantilRestauradaTest extends TestCase
{
    private bool $isolated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('INTELECTA_RESTORED_IDENTITY_DB');
        if (! $database) {
            $this->markTestSkipped('Requiere restauración aislada explícita; nunca usa intelecta.');
        }
        $this->assertNotEmpty(getenv('INTELECTA_DEMO_EXISTING_PASSWORD'), 'Credencial previa requerida por entorno privado del proceso.');
        if (! preg_match('/^intelecta_isolated_[a-z0-9_]+$/', $database)) {
            $this->fail('Nombre de restauración inseguro.');
        }
        config(['database.default' => 'restored_identity', 'database.connections.restored_identity' => [
            'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => 55439, 'database' => $database,
            'username' => 'codex', 'password' => '', 'charset' => 'utf8', 'prefix' => '', 'search_path' => 'public', 'sslmode' => 'prefer',
        ]]);
        $this->assertSame($database, DB::selectOne('SELECT current_database() AS name')->name);
        DB::beginTransaction();
        $this->isolated = true;
    }

    protected function tearDown(): void
    {
        if ($this->isolated) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        parent::tearDown();
    }

    public function test_every_real_student_account_on_the_restored_snapshot_can_login_and_obeys_its_administrative_decision(): void
    {
        $audit = app(AuditoriaIdentidadService::class);
        $before = $audit->generar();
        $this->assertSame(72, $before['resumen']['expedientes']);
        $this->assertSame(3, $before['resumen']['cuentas_estudiante']);
        $this->assertSame(62, $before['resumen']['evaluacion_administrativa_permitida']);
        $this->assertSame([403, 403, 403], array_column($before['cuentas'], 'middleware_identidad_http'));
        $unchangedTables = ['users', 'inscripciones_academicas', 'matriculas_academicas', 'habilitaciones_academicas', 'cuotas_academicas', 'model_has_roles', 'role_has_permissions', 'model_has_permissions', 'migrations'];
        $snapshot = fn () => collect($unchangedTables)->mapWithKeys(fn ($table) => [$table => hash('sha256', DB::table($table)->orderByRaw('1')->get()->toJson())])->all();
        $protected = $snapshot();
        $rows = array_values(array_filter($before['filas'], fn ($row) => $row['clasificacion'] === 'A'));
        foreach ($rows as &$row) {
            $row['revision'] = 'aprobado'; // Solo autoriza la simulación aislada; NO autoriza la base principal.
        }
        unset($row);
        $service = app(ConciliacionIdentidadService::class);
        $manifest = ['destino' => $service->destino(), 'filas' => $rows];
        $service->ejecutar($manifest); // Primero dry-run, incluso en la copia.
        $service->ejecutar($manifest, true, User::role('Super Administrador')->firstOrFail(), 'Ensayo técnico aislado restaurado, rollback al finalizar');
        $this->assertSame(['ya_vinculado_sin_cambios', 'ya_vinculado_sin_cambios', 'ya_vinculado_sin_cambios'],
            array_column($service->ejecutar($manifest, true, User::role('Super Administrador')->firstOrFail(), 'Reejecución aislada sin sobrescritura'), 'resultado'));
        $this->assertSame($protected, $snapshot(), 'El lote no debe cambiar credenciales, roles, finanzas ni migraciones.');
        $after = $audit->generar();
        $this->assertSame([200, 200, 200], array_column($after['cuentas'], 'middleware_identidad_http'));
        $this->assertSame([true, true, false], array_column($after['cuentas'], 'evaluacion_permitida'));
        $template = PlantillaEvaluacion::where('estado_plan', 'activa')->firstOrFail();
        $evidence = [];
        foreach (User::role('Estudiante')->orderBy('id')->get() as $user) {
            $this->post('/logout');
            $this->post('/login', ['email' => $user->email, 'password' => getenv('INTELECTA_DEMO_EXISTING_PASSWORD')])->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->get('/estudiante/mi-ficha')->assertOk();
            $this->get('/estudiante/ranking')->assertOk();
            $allowed = collect($after['cuentas'])->firstWhere('user_id', $user->id)['evaluacion_permitida'];
            $this->get('/estudiante/evaluaciones')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/Evaluaciones')->where('postulanteVinculado', true)->where('accesoEvaluaciones.permitido', $allowed));
            $attempt = $this->postJson('/estudiante/evaluaciones/'.$template->id_plan.'/iniciar');
            $allowed ? $attempt->assertRedirect() : $attempt->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
            $evidence[] = ['user_id' => $user->id, 'id_post' => $user->postulante->id_post, 'login' => 'OK', 'ficha' => 200, 'ranking' => 200,
                'evaluaciones' => 200, 'inicio_evaluacion' => $attempt->getStatusCode(), 'permitido' => $allowed];
        }
        // Login/logout pueden rehashear o rotar remember_token según Laravel.
        // Eso es autenticación normal de esta copia transaccional, no conciliación.
        $this->assertSame(collect($protected)->except('users')->all(), collect($snapshot())->except('users')->all(), 'Roles, finanzas o migraciones cambiaron.');
        $this->assertSame(69, $after['resumen']['expedientes_sin_cuenta_asignada']);
        fwrite(STDOUT, "\nEVIDENCIA_RESTAURACION_SOLO_EN_TRANSACCION=".json_encode($evidence, JSON_UNESCAPED_UNICODE)."\n");
    }
}
