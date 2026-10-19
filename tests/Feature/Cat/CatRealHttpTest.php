<?php

namespace Tests\Feature\Cat;

use App\Domains\Cat\Exceptions\MotorCatNoDisponible;
use App\Domains\Cat\Models\IntentoCat;
use App\Domains\Cat\Services\MotorCatHttp;
use App\Domains\Cat\Services\PrepararInstrumentoDemo;
use App\Domains\Evaluaciones\Models\Alternativa;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\Support\ContextoCatHttp;
use Tests\TestCase;

// Opt-in: red real a FastAPI; fixtures y migraciones exclusivamente en SQLite :memory:.
class CatRealHttpTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.url', null);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('CAT_REAL_TESTS') !== '1') {
            $this->markTestSkipped('Opt-in CAT_REAL_TESTS=1 y FastAPI real configurado.');
        }
        $this->assertNotEmpty(config('services.cat.token'), 'Falta configurar el secreto local del servicio.');
        Role::create(['name' => 'Estudiante', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Estudiante');
        Postulante::factory()->withUser($user)->create();
        $m = Materia::create(['codigo_mat' => 'MAT', 'nombre_mat' => 'Matemática', 'estado_mat' => 'activo']);
        $a = AreaConocimiento::create(['id_mat' => $m->id_mat, 'nombre_area' => 'Área de prueba', 'estado_area' => 'activo']);
        $t = Tema::create(['id_area' => $a->id_area, 'nombre_tem' => 'Tema', 'estado_tem' => 'activo']);
        foreach (range(1, 16) as $id) {
            $q = Pregunta::create(['id_tem' => $t->id_tem, 'enunciado_preg' => 'Ejercicio de prueba '.$id,
                'estado_preg' => 'activo', 'tipo_preg' => 'opcion_multiple', 'puntaje_preg' => 1]);
            foreach (range(1, 5) as $n) {
                Alternativa::create(['id_preg' => $q->id_preg, 'texto_alt' => 'Opción '.$n, 'letra_alt' => chr(64 + $n),
                    'orden_alt' => $n, 'estado_alt' => 'activo', 'es_correcta_alt' => $n === 1]);
            }
        }
        app(PrepararInstrumentoDemo::class)->ejecutar();
        $this->actingAs($user);
    }

    private function iniciar(): IntentoCat
    {
        $this->post(route('estudiante.cat.iniciar', 1))->assertRedirect();
        $i = IntentoCat::firstOrFail();
        $this->assertSame(8, $i->id_preg_actual);
        $this->assertSame('cat-d1-eap-v1', $i->version_motor);

        return $i;
    }

    private function respuesta(IntentoCat $i): array
    {
        $i->refresh();
        $item = collect($i->snapshot['items'])->firstWhere('id_preg', $i->id_preg_actual);

        return ['id_preg' => $item['id_preg'], 'id_alt' => $item['alternativas'][0]['id_alt'], 'revision' => $i->revision];
    }

    public function test_real_complete_assessment_reload_duplicate_and_stale_revision(): void
    {
        $i = $this->iniciar();
        $sequence = [];
        foreach (range(1, 10) as $n) {
            $data = $this->respuesta($i);
            $sequence[] = $data['id_preg'];
            $url = route('estudiante.cat.responder', $i->id_intento_cat);
            $this->postJson($url, $data)->assertRedirect();
            $this->postJson($url, $data)->assertRedirect();
            $this->postJson($url, array_replace($data, ['id_alt' => $data['id_alt'] + 1]))->assertConflict();
            $this->assertSame($n, $i->fresh()->revision);
            $this->get(route('estudiante.cat.show', $i->id_intento_cat))->assertInertia(fn (Assert $p) => $p
                ->where('intento.revision', $n)->missing('intento.snapshot')->missing('intento.pregunta.parametros')
                ->where('intento.resultado_cat', fn ($result) => $n === 10 || $result === null));
        }
        $i->refresh();
        $this->assertSame(10, count(array_unique($sequence)));
        $this->assertSame([8, 12], array_slice($sequence, 0, 2));
        $this->assertSame('finalizada', $i->estado);
        $this->assertSame('Máximo de preguntas alcanzado', $i->resultado_cat['motivo']);
        $this->assertDatabaseCount('respuestas_cat', 10);
        $this->assertDatabaseCount('evaluaciones_aplicadas', 0);
        $this->assertDatabaseCount('rendimientos_postulante', 0);
    }

    public function test_bad_token_keeps_accepted_response_recoverable(): void
    {
        $i = $this->iniciar();
        $token = config('services.cat.token');
        config(['services.cat.token' => 'token-incorrecto-exclusivo-prueba-negativa']);
        try {
            $this->postJson(route('estudiante.cat.responder', $i->id_intento_cat), $this->respuesta($i))->assertRedirect();
            $this->assertSame('http_401', $i->fresh()->error_motor);
            $this->assertDatabaseCount('respuestas_cat', 1);
        } finally {
            config(['services.cat.token' => $token]);
        }
        $this->post(route('estudiante.cat.recuperar', $i->id_intento_cat))->assertRedirect();
        $this->assertSame(12, $i->fresh()->id_preg_actual);
        $this->assertSame(1, $i->fresh()->revision);
    }

    public function test_unreachable_motor_keeps_answer_and_real_motor_reconstructs_it(): void
    {
        $i = $this->iniciar();
        $url = config('services.cat.url');
        config(['services.cat.url' => 'http://127.0.0.1:18010', 'services.cat.timeout' => 1]);
        try {
            $this->postJson(route('estudiante.cat.responder', $i->id_intento_cat), $this->respuesta($i))->assertRedirect();
            $this->assertTrue($i->fresh()->calculo_pendiente);
            $this->assertDatabaseCount('respuestas_cat', 1);
        } finally {
            config(['services.cat.url' => $url]);
        }
        $this->post(route('estudiante.cat.recuperar', $i->id_intento_cat))->assertRedirect();
        $this->assertSame(12, $i->fresh()->id_preg_actual);
        $this->assertDatabaseCount('respuestas_cat', 1);
    }

    public function test_deadline_blocks_new_answer_with_real_motor(): void
    {
        $i = $this->iniciar();
        $data = $this->respuesta($i);
        $this->travelTo($i->vence_en);
        $this->postJson(route('estudiante.cat.responder', $i->id_intento_cat), $data)->assertRedirect();
        $this->assertSame('vencida', $i->fresh()->estado);
        $this->assertDatabaseCount('respuestas_cat', 0);
    }

    public function test_real_python_rejects_forged_history_sequence(): void
    {
        try {
            app(MotorCatHttp::class)->calcular(ContextoCatHttp::crear([['id_preg' => 1, 'correcta' => true, 'orden' => 1]]));
            $this->fail('La primera pregunta real debe ser 8.');
        } catch (MotorCatNoDisponible $e) {
            $this->assertSame('http_409', $e->codigo);
        }
    }

    public function test_real_eap_branches_match_the_verified_contract(): void
    {
        foreach ([[true, 12, 0.38262, 0.90995], [false, 4, -0.58851, 0.83248]] as [$correcta, $next, $theta, $sd]) {
            $decision = app(MotorCatHttp::class)->calcular(ContextoCatHttp::crear([['id_preg' => 8, 'correcta' => $correcta, 'orden' => 1]]));
            $this->assertSame($next, $decision->siguienteId);
            $this->assertEqualsWithDelta($theta, $decision->theta, 0.00002);
            $this->assertEqualsWithDelta($sd, $decision->sd, 0.00002);
        }
    }
}
