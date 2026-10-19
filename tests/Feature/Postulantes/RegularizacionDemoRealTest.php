<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Verifica datos YA regularizados. Sin migraciones, seeders ni altas; HTTP bajo rollback. */
class RegularizacionDemoRealTest extends TestCase
{
    private bool $started = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('INTELECTA_VERIFY_ALL_LIVE') !== '1' || ! getenv('INTELECTA_DEMO_ACCESS_PACKAGE')) {
            $this->markTestSkipped('Opt-in explícito y paquete privado para verificar la principal regularizada.');
        }
        $this->assertNotEmpty(getenv('INTELECTA_DEMO_EXISTING_PASSWORD'), 'La credencial previa solo se proporciona por entorno del proceso.');
        $env = Dotenv::parse(file_get_contents(base_path('.env')));
        $this->assertSame('pgsql', $env['DB_CONNECTION']);
        $this->assertSame('intelecta', $env['DB_DATABASE']);
        $this->assertSame('127.0.0.1', $env['DB_HOST']);
        $this->assertSame('5432', $env['DB_PORT']);
        config(['database.default' => 'live_demo_regularized', 'database.connections.live_demo_regularized' => [
            'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => '5432', 'database' => 'intelecta',
            'username' => $env['DB_USERNAME'], 'password' => $env['DB_PASSWORD'] ?? '',
            'charset' => 'utf8', 'prefix' => '', 'search_path' => 'public', 'sslmode' => 'prefer',
        ], 'logging.default' => 'null', 'app.debug' => false, 'hashing.rehash_on_login' => false]);
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

    private function snapshot(): array
    {
        $result = [];
        foreach (DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename") as $row) {
            $table = DB::connection()->getQueryGrammar()->wrapTable($row->tablename);
            $state = DB::selectOne("SELECT count(*) AS n, md5(coalesce(string_agg(row_to_json(t)::text, '' ORDER BY row_to_json(t)::text COLLATE \"C\"), '')) AS hash FROM {$table} t");
            $result[$row->tablename] = [(int) $state->n, $state->hash];
        }

        return $result;
    }

    public function test_all_72_actual_accounts_login_and_follow_academic_authorization_without_persisting_test_passwords(): void
    {
        $before = $this->snapshot();
        $this->assertSame(78, User::count());
        $this->assertSame(72, User::role('Estudiante')->count());
        $this->assertSame(72, Postulante::count());
        $this->assertSame(72, Postulante::whereNotNull('user_id')->count());
        $this->assertSame(72, Postulante::distinct()->count('user_id'));
        $payload = json_decode(Crypt::decryptString(file_get_contents(getenv('INTELECTA_DEMO_ACCESS_PACKAGE'))), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('intelecta', $payload['destino']['database']);
        $credentials = collect($payload['cuentas'])->keyBy('user_id');
        $this->assertCount(69, $credentials);
        $this->assertSame(range(4, 72), $credentials->pluck('id_post')->sort()->values()->all());
        $template = PlantillaEvaluacion::where('estado_plan', 'activa')->firstOrFail();
        $permitted = 0;
        $restricted = 0;
        $students = User::role('Estudiante')->orderBy('id')->get();
        foreach ($students as $user) {
            $this->post('/logout');
            $password = getenv('INTELECTA_DEMO_EXISTING_PASSWORD');
            if ($credentials->has($user->id)) {
                $credential = $credentials->get($user->id);
                $this->assertSame($user->email, $credential['correo']);
                $this->assertTrue(Password::broker()->tokenExists($user, $credential['token']), 'El acceso temporal debe seguir vigente antes del ensayo.');
                $password = 'Ensayo!Aa2026-'.Str::random(16);
                $this->post('/reset-password', ['email' => $user->email, 'token' => $credential['token'],
                    'password' => $password, 'password_confirmation' => $password])->assertRedirect('/login');
                $this->assertFalse(Password::broker()->tokenExists($user, $credential['token']));
            }
            $this->post('/login', ['email' => $user->email, 'password' => $password])->assertRedirect();
            $this->assertAuthenticatedAs($user);
            $this->assertSame(['Estudiante'], $user->getRoleNames()->all());
            $post = $user->postulante;
            $this->assertNotNull($post);
            $this->assertTrue($post->matriculasAcademicas()->exists());
            $this->assertTrue($post->inscripcionesAcademicas()->exists());
            $decision = app(AccesoEvaluacionService::class)->evaluar($post);
            $expected = $post->estado_post === 'activo' && $post->habilitacionesAcademicas->last()->habilitado_evaluaciones_hab;
            $this->assertSame($expected, $decision['permitido']);
            $this->get('/estudiante/mi-ficha')->assertOk();
            $this->get('/estudiante/ranking')->assertOk();
            $this->get('/estudiante/evaluaciones')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/Evaluaciones')->where('postulanteVinculado', true)->where('accesoEvaluaciones.permitido', $expected));
            $start = $this->postJson('/estudiante/evaluaciones/'.$template->id_plan.'/iniciar');
            if ($expected) {
                $start->assertRedirect();
                $permitted++;
            } else {
                $start->assertUnprocessable();
                $restricted++;
            }
            if ($post->id_post === 3) {
                $this->assertFalse($expected);
                $start->assertJsonValidationErrors('habilitacion');
            }
        }
        $this->assertSame(62, $permitted);
        $this->assertSame(10, $restricted);
        $afterHttp = $this->snapshot();
        foreach (['inscripciones_academicas', 'matriculas_academicas', 'cuotas_academicas', 'habilitaciones_academicas', 'model_has_roles', 'role_has_permissions', 'model_has_permissions', 'migrations', 'postulantes'] as $table) {
            $this->assertSame($before[$table], $afterHttp[$table], $table.' cambió durante el ensayo HTTP.');
        }
        $this->assertSame(1, DB::transactionLevel());
        DB::rollBack();
        $this->started = false;
        $this->assertSame($before, $this->snapshot(), 'Todos los cambios HTTP, contraseñas y consumos de tokens deben quedar revertidos.');
        fwrite(STDOUT, "\nPRINCIPAL_72_VERIFICADA=".json_encode(['cuentas' => 72, 'vinculos_unicos' => 72,
            'login_y_portal_ok' => 72, 'evaluaciones_permitidas' => $permitted, 'evaluaciones_restringidas' => $restricted,
            'tablas_intactas_tras_rollback' => count($before), 'secretos_publicados' => false])."\n");
    }
}
