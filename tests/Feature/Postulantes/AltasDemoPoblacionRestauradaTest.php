<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\AltasDemoEstudianteService;
use App\Domains\Postulantes\Services\AuditoriaIdentidadService;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Opt-in: aplica las 69 altas SOLO sobre una restauración, dentro de rollback. */
class AltasDemoPoblacionRestauradaTest extends TestCase
{
    private bool $started = false;

    private ?string $package = null;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('INTELECTA_DEMO_69_RESTORED_DB');
        if (! $database) {
            $this->markTestSkipped('Requiere restauración aislada explícita para ensayar las 72 cuentas.');
        }
        $this->assertNotEmpty(getenv('INTELECTA_DEMO_EXISTING_PASSWORD'), 'Credencial previa requerida por entorno privado del proceso.');
        $this->assertMatchesRegularExpression('/^intelecta_isolated_[a-z0-9_]+$/', $database);
        config(['database.default' => 'demo_69_restored', 'database.connections.demo_69_restored' => [
            'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => 55439, 'database' => $database,
            'username' => 'codex', 'password' => '', 'charset' => 'utf8', 'prefix' => '', 'search_path' => 'public', 'sslmode' => 'prefer',
        ], 'logging.default' => 'null']);
        $this->assertSame($database, DB::selectOne('SELECT current_database() AS name')->name);
        DB::beginTransaction();
        $this->started = true;
        $this->package = storage_path('app/private/test-poblacion-72-'.Str::uuid().'.enc');
    }

    protected function tearDown(): void
    {
        if ($this->started) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        if ($this->package && is_file($this->package)) {
            unlink($this->package);
        }
        parent::tearDown();
    }

    public function test_69_proposed_accounts_and_all_72_logins_portals_and_evaluation_decisions_on_the_restored_copy(): void
    {
        $service = app(AltasDemoEstudianteService::class);
        $manifest = $service->propuesta();
        $this->assertCount(69, $manifest['filas']);
        $this->assertSame(60, collect($manifest['filas'])->where('evaluacion_permitida', true)->count());
        $this->assertSame(9, collect($manifest['filas'])->where('evaluacion_permitida', false)->count());
        $protectedTables = ['inscripciones_academicas', 'matriculas_academicas', 'cuotas_academicas', 'habilitaciones_academicas', 'role_has_permissions', 'model_has_permissions', 'migrations'];
        $snapshot = fn () => collect($protectedTables)->mapWithKeys(fn ($table) => [$table => hash('sha256', DB::table($table)->orderByRaw('1')->get()->toJson())])->all();
        $before = $snapshot();
        $existingUsers = hash('sha256', DB::table('users')->orderBy('id')->get()->toJson());
        $service->ejecutar($manifest);
        $this->assertSame(9, User::count());
        foreach ($manifest['filas'] as &$row) {
            $row['revision'] = 'aprobado'; // Ensayo en copia, NO autorización para la principal.
        }
        unset($row);
        $actor = User::findOrFail(1);
        $this->actingAs($actor);
        $result = $service->ejecutar($manifest, true, $actor, 'Ensayo aislado de las 69 altas propuestas; rollback obligatorio', $this->package);
        $this->assertCount(69, $result);
        $this->assertSame($existingUsers, hash('sha256', DB::table('users')->where('id', '<=', 9)->orderBy('id')->get()->toJson()));
        $this->assertSame($before, $snapshot());
        $this->assertSame(78, User::count());
        $this->assertSame(72, User::role('Estudiante')->count());
        $this->assertSame(72, Postulante::count());
        $this->assertSame(72, Postulante::whereNotNull('user_id')->count());
        $this->assertSame(72, Postulante::distinct()->count('user_id'));
        $this->assertSame(69, DB::table('bitacora_sistema')->where('accion', 'alta_demo_estudiante')->where('user_id', 1)->count());
        $tokenHashes = hash('sha256', DB::table('password_reset_tokens')->orderBy('email')->get()->toJson());
        $again = $service->ejecutar($manifest, true, $actor, 'Reejecución aislada de altas sin sobrescribir datos', $this->package);
        $this->assertSame(69, collect($again)->where('resultado', 'ya_creada_sin_cambios')->count());
        $this->assertSame($tokenHashes, hash('sha256', DB::table('password_reset_tokens')->orderBy('email')->get()->toJson()));
        $credentials = collect(json_decode(Crypt::decryptString(file_get_contents($this->package)), true, flags: JSON_THROW_ON_ERROR)['cuentas'])->keyBy('user_id');
        $template = PlantillaEvaluacion::where('estado_plan', 'activa')->firstOrFail();
        $allowedCount = 0;
        $deniedCount = 0;
        foreach (User::role('Estudiante')->orderBy('id')->get() as $user) {
            $this->post('/logout');
            $password = getenv('INTELECTA_DEMO_EXISTING_PASSWORD');
            if ($credentials->has($user->id)) {
                $record = $credentials->get($user->id);
                $password = 'Temporal!Aa2026-'.Str::random(12);
                $this->post('/reset-password', ['email' => $user->email, 'token' => $record['token'], 'password' => $password, 'password_confirmation' => $password])->assertRedirect('/login');
            }
            $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->assertSame(['Estudiante'], $user->getRoleNames()->all());
            $post = $user->postulante;
            $this->assertNotNull($post);
            $this->assertTrue($post->matriculasAcademicas()->exists());
            $this->assertTrue($post->inscripcionesAcademicas()->exists());
            $allowed = app(AccesoEvaluacionService::class)->evaluar($post)['permitido'];
            $this->get('/estudiante/mi-ficha')->assertOk();
            $this->get('/estudiante/ranking')->assertOk();
            $this->get('/estudiante/evaluaciones')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/Evaluaciones')->where('postulanteVinculado', true)->where('accesoEvaluaciones.permitido', $allowed));
            $response = $this->postJson('/estudiante/evaluaciones/'.$template->id_plan.'/iniciar');
            if ($allowed) {
                $response->assertRedirect();
                $allowedCount++;
            } else {
                $response->assertUnprocessable();
                $deniedCount++;
            }
        }
        $this->assertSame(62, $allowedCount);
        $this->assertSame(10, $deniedCount);
        $this->assertSame($before, $snapshot());
        $report = app(AuditoriaIdentidadService::class)->generar();
        $this->assertSame(0, $report['resumen']['cuentas_sin_vinculo']);
        $this->assertSame(0, $report['resumen']['expedientes_sin_cuenta_asignada']);
        fwrite(STDOUT, "\nENSAYO_RESTAURACION_72=".json_encode(['cuentas_estudiante' => 72, 'login_y_portal_ok' => 72, 'inicio_permitido' => $allowedCount, 'inicio_restringido' => $deniedCount, 'finanzas_intactas' => true, 'rollback_obligatorio' => true])."\n");
    }
}
