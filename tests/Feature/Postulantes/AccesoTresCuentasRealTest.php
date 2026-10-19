<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Opt-in explícito; verifica los tres vínculos YA autorizados. Todo HTTP se revierte. */
class AccesoTresCuentasRealTest extends TestCase
{
    private bool $started = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('INTELECTA_VERIFY_THREE_LIVE') !== '1') {
            $this->markTestSkipped('Verificación real opt-in de los tres vínculos aprobados.');
        }
        $this->assertNotEmpty(getenv('INTELECTA_DEMO_EXISTING_PASSWORD'), 'Proporciona la credencial previa solo por entorno del proceso, nunca en Git.');
        $env = Dotenv::parse(file_get_contents(base_path('.env')));
        $this->assertSame('pgsql', $env['DB_CONNECTION']);
        $this->assertSame('intelecta', $env['DB_DATABASE']);
        config(['database.default' => 'live_identity_check', 'database.connections.live_identity_check' => [
            'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => 5432, 'database' => 'intelecta',
            'username' => $env['DB_USERNAME'], 'password' => $env['DB_PASSWORD'] ?? '',
            'charset' => 'utf8', 'prefix' => '', 'search_path' => 'public', 'sslmode' => 'prefer',
        ], 'logging.default' => 'null']);
        DB::beginTransaction();
        $this->started = true;
    }

    protected function tearDown(): void
    {
        if ($this->started) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        parent::tearDown();
    }

    public function test_the_three_authorized_accounts_access_the_real_portal_without_changing_financial_rules(): void
    {
        $protectedTables = ['inscripciones_academicas', 'matriculas_academicas', 'cuotas_academicas', 'habilitaciones_academicas', 'model_has_roles', 'role_has_permissions', 'model_has_permissions', 'migrations'];
        $snapshot = fn () => collect($protectedTables)->mapWithKeys(fn ($table) => [$table => hash('sha256', DB::table($table)->orderByRaw('1')->get()->toJson())])->all();
        $before = $snapshot();
        $template = PlantillaEvaluacion::where('estado_plan', 'activa')->firstOrFail();
        $evidence = [];
        foreach ([7 => [1, true], 8 => [2, true], 9 => [3, false]] as $uid => [$pid, $allowed]) {
            $user = User::findOrFail($uid);
            $this->assertSame($pid, $user->postulante->id_post);
            $this->assertTrue($user->cuentaActiva());
            $this->post('/logout');
            $this->post('/login', ['email' => $user->email, 'password' => getenv('INTELECTA_DEMO_EXISTING_PASSWORD')])->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->get('/estudiante/mi-ficha')->assertOk();
            $this->get('/estudiante/ranking')->assertOk();
            $this->get('/estudiante/evaluaciones')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/Evaluaciones')->where('postulanteVinculado', true)->where('accesoEvaluaciones.permitido', $allowed));
            $response = $this->postJson('/estudiante/evaluaciones/'.$template->id_plan.'/iniciar');
            $allowed ? $response->assertRedirect() : $response->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
            $evidence[] = ['user_id' => $uid, 'id_post' => $pid, 'login' => 'OK', 'portal' => 200, 'inicio' => $response->getStatusCode(), 'permitido' => $allowed];
        }
        $this->assertSame($before, $snapshot());
        $this->assertSame(1, DB::transactionLevel());
        fwrite(STDOUT, "\nACCESO_REAL_TRES_CUENTAS_HTTP_REVERTIDO=".json_encode($evidence)."\n");
    }
}
