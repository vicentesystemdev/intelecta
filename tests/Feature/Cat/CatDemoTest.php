<?php

namespace Tests\Feature\Cat;

use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Cat\Contracts\MotorCat;
use App\Domains\Cat\DTOs\DecisionCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;
use App\Domains\Cat\Models\InstrumentoCat;
use App\Domains\Cat\Models\IntentoCat;
use App\Domains\Cat\Services\IntentoCatService;
use App\Domains\Cat\Services\MotorCatPendiente;
use App\Domains\Cat\Services\PrepararInstrumentoDemo;
use App\Domains\Evaluaciones\Models\Alternativa;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use LogicException;
use Spatie\Permission\Models\Role;
use Tests\Support\MotorCatSimulado;
use Tests\TestCase;

class CatDemoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Postulante $postulante;

    private InstrumentoCat $instrumento;

    private MotorCatSimulado $motor;

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
        Role::create(['name' => 'Estudiante', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole('Estudiante');
        $this->postulante = Postulante::factory()->withUser($this->user)->create();
        $materia = Materia::create(['codigo_mat' => 'MAT', 'nombre_mat' => 'Matemática', 'estado_mat' => 'activo']);
        $area = AreaConocimiento::create(['id_mat' => $materia->id_mat, 'nombre_area' => 'Álgebra', 'estado_area' => 'activo']);
        $tema = Tema::create(['id_area' => $area->id_area, 'nombre_tem' => 'Tema de prueba', 'estado_tem' => 'activo']);
        foreach (range(1, 16) as $n) {
            $pregunta = Pregunta::create(['id_tem' => $tema->id_tem, 'enunciado_preg' => 'Enunciado original '.$n,
                'tipo_preg' => 'opcion_multiple', 'estado_preg' => 'activo', 'puntaje_preg' => 1]);
            foreach (range(1, 5) as $j) {
                Alternativa::create(['id_preg' => $pregunta->id_preg, 'texto_alt' => 'Alternativa '.$j,
                    'letra_alt' => chr(64 + $j), 'orden_alt' => $j, 'es_correcta_alt' => $j === 1, 'estado_alt' => 'activo']);
            }
        }
        $this->instrumento = app(PrepararInstrumentoDemo::class)->ejecutar();
        $this->motor = new MotorCatSimulado;
        $this->app->instance(MotorCat::class, $this->motor);
        $this->actingAs($this->user);
    }

    private function iniciar(): IntentoCat
    {
        $this->post(route('estudiante.cat.iniciar', $this->instrumento->id_instrumento_cat))->assertRedirect();

        return IntentoCat::latest('id_intento_cat')->firstOrFail();
    }

    private function respuesta(IntentoCat $intento): array
    {
        $intento->refresh();
        $item = collect($intento->snapshot['items'])->firstWhere('id_preg', $intento->id_preg_actual);

        return ['id_preg' => $item['id_preg'], 'id_alt' => $item['alternativas'][0]['id_alt'], 'revision' => $intento->revision];
    }

    public function test_instrument_is_idempotent_exact_and_does_not_modify_academic_tables(): void
    {
        $before = Pregunta::all()->toJson();
        $again = app(PrepararInstrumentoDemo::class)->ejecutar();
        $this->assertSame($this->instrumento->id_instrumento_cat, $again->id_instrumento_cat);
        $this->assertSame(range(1, 16), $again->preguntas()->pluck('preguntas.id_preg')->all());
        $this->assertSame(30, $again->duracion_minutos);
        $this->assertSame(10, $again->configuracion['maximum']);
        $this->assertSame('simulados-demo-v1', $again->version_parametros);
        $this->assertSame($before, Pregunta::all()->toJson());
        $this->assertDatabaseCount('plantillas_evaluacion', 0);
        $this->assertDatabaseCount('evaluaciones_aplicadas', 0);
    }

    public function test_snapshot_survives_edits_and_deleted_alternatives_without_leaking_keys(): void
    {
        $intento = $this->iniciar();
        $data = $this->respuesta($intento);
        Pregunta::findOrFail(1)->update(['enunciado_preg' => 'Contenido cambiado']);
        Alternativa::where('id_preg', 1)->delete();
        $this->get(route('estudiante.cat.show', $intento->id_intento_cat))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Estudiante/CatDemo')->where('intento.pregunta.enunciado', 'Enunciado original 1')
            ->missing('intento.snapshot')->missing('intento.pregunta.alternativas.0.correcta')
            ->missing('intento.pregunta.parametros')->where('intento.resultado_cat', null));
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $data + ['correcta' => false])->assertRedirect();
        $this->assertDatabaseHas('respuestas_cat', ['id_intento_cat' => $intento->id_intento_cat, 'id_alt' => $data['id_alt'], 'correcta' => true]);
        $this->assertSame(1, $intento->fresh()->revision);
        $this->assertSame('Enunciado original 1', $intento->fresh()->snapshot['items'][0]['enunciado']);
    }

    public function test_duplicate_response_is_replayed_and_conflicting_revision_is_rejected(): void
    {
        $intento = $this->iniciar();
        $this->iniciar();
        $this->assertDatabaseCount('intentos_cat', 1);
        $data = $this->respuesta($intento);
        $url = route('estudiante.cat.responder', $intento->id_intento_cat);
        $this->postJson($url, $data)->assertRedirect();
        $this->postJson($url, $data)->assertRedirect();
        $this->postJson($url, array_replace($data, ['id_alt' => $data['id_alt'] + 1]))->assertConflict();
        $this->assertDatabaseCount('respuestas_cat', 1);
        $this->assertSame(1, $intento->fresh()->revision);
    }

    public function test_future_question_foreign_alternative_and_stale_revision_are_rejected(): void
    {
        $intento = $this->iniciar();
        $data = $this->respuesta($intento);
        $url = route('estudiante.cat.responder', $intento->id_intento_cat);
        $this->postJson($url, array_replace($data, ['id_preg' => 2]))->assertConflict();
        $this->postJson($url, array_replace($data, ['id_alt' => 6]))->assertUnprocessable();
        $this->postJson($url, array_replace($data, ['revision' => 1]))->assertConflict();
        $this->assertDatabaseCount('respuestas_cat', 0);
    }

    public function test_response_is_persisted_before_motor_call_and_recovered_after_failure(): void
    {
        $intento = $this->iniciar();
        $this->motor->interceptar = function ($contexto) {
            $this->assertDatabaseHas('respuestas_cat', ['id_intento_cat' => $contexto->id_intento_cat, 'revision' => 1]);
            throw new MotorCatNoDisponible;
        };
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertRedirect();
        $this->assertTrue($intento->fresh()->calculo_pendiente);
        $this->get(route('estudiante.cat.show', $intento->id_intento_cat))->assertOk()->assertInertia(fn (Assert $p) => $p
            ->where('intento.revision', 1)->where('intento.pregunta', null)->where('intento.calculo_pendiente', true));
        $this->motor->interceptar = null;
        $this->post(route('estudiante.cat.recuperar', $intento->id_intento_cat))->assertRedirect();
        $this->assertSame(2, $intento->fresh()->id_preg_actual);
        $this->assertDatabaseCount('respuestas_cat', 1);
    }

    public function test_deadline_is_authoritative_at_exact_boundary_and_survives_reload(): void
    {
        $intento = $this->iniciar();
        $data = $this->respuesta($intento);
        $this->travelTo($intento->vence_en);
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $data)->assertRedirect();
        $this->assertSame('vencida', $intento->fresh()->estado);
        $this->assertDatabaseCount('respuestas_cat', 0);
        $this->get(route('estudiante.cat.show', $intento->id_intento_cat))->assertInertia(fn (Assert $p) => $p
            ->where('intento.estado', 'vencida')->where('intento.pregunta', null)->where('intento.resultado_cat', null));
    }

    public function test_habilitation_is_rechecked_before_submission_and_recovery(): void
    {
        $intento = $this->iniciar();
        HabilitacionAcademica::create(['id_post' => $this->postulante->id_post, 'estado_hab' => 'restringido', 'habilitado_evaluaciones_hab' => false]);
        $this->postJson(route('estudiante.cat.iniciar', $this->instrumento->id_instrumento_cat))->assertUnprocessable();
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertUnprocessable();
        $this->postJson(route('estudiante.cat.recuperar', $intento->id_intento_cat))->assertUnprocessable();
        $this->get(route('estudiante.cat.show', $intento->id_intento_cat))->assertInertia(fn (Assert $p) => $p
            ->where('acceso.permitido', false)->where('intento.pregunta', null));
        $this->assertDatabaseCount('respuestas_cat', 0);
    }

    public function test_other_student_cannot_read_reply_or_recover_attempt(): void
    {
        $intento = $this->iniciar();
        $data = $this->respuesta($intento);
        $other = User::factory()->create();
        $other->assignRole('Estudiante');
        Postulante::factory()->withUser($other)->create();
        $this->actingAs($other)->get(route('estudiante.cat.show', $intento->id_intento_cat))->assertNotFound();
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $data)->assertNotFound();
        $this->postJson(route('estudiante.cat.recuperar', $intento->id_intento_cat))->assertNotFound();
        $this->assertDatabaseCount('respuestas_cat', 0);
    }

    public function test_guest_roleless_and_unlinked_users_cannot_start(): void
    {
        $this->app['auth']->forgetGuards();
        $this->get(route('estudiante.cat.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('estudiante.cat.iniciar', 1))->assertForbidden();
        $unlinked = User::factory()->create();
        $unlinked->assignRole('Estudiante');
        $this->actingAs($unlinked)->post(route('estudiante.cat.iniciar', 1))->assertForbidden();
    }

    public function test_finalization_at_ten_is_separate_idempotent_and_cannot_accept_more(): void
    {
        $intento = $this->iniciar();
        foreach (range(1, 10) as $n) {
            $data = $this->respuesta($intento);
            $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $data)->assertRedirect();
        }
        $this->assertSame('finalizada', $intento->fresh()->estado);
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $data)->assertRedirect();
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), ['revision' => 10, 'id_preg' => 11, 'id_alt' => 51])->assertConflict();
        $this->get(route('estudiante.cat.show', $intento->id_intento_cat))->assertInertia(fn (Assert $p) => $p
            ->where('intento.pregunta', null)->where('intento.resultado_cat.experimental', true)->where('intento.aciertos', 10));
        $this->assertDatabaseCount('respuestas_cat', 10);
        $this->assertDatabaseCount('evaluaciones_aplicadas', 0);
        $this->assertDatabaseCount('rendimientos_postulante', 0);
    }

    public function test_minimum_eight_can_stop_on_uncertainty(): void
    {
        $this->motor->pararEn = 8;
        $intento = $this->iniciar();
        foreach (range(1, 8) as $n) {
            $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertRedirect();
        }
        $this->assertSame('finalizada', $intento->fresh()->estado);
        $this->assertSame(8, $intento->fresh()->revision);
    }

    public function test_invalid_early_or_repeated_motor_decisions_are_not_applied(): void
    {
        $this->motor->pararEn = 0;
        $intento = $this->iniciar();
        $this->assertTrue($intento->calculo_pendiente);
        $this->assertNull($intento->fresh()->id_preg_actual);
        $this->motor->pararEn = 10;
        $this->post(route('estudiante.cat.recuperar', $intento->id_intento_cat))->assertRedirect();
        $this->motor->interceptar = fn ($c) => new DecisionCat(1, false, 0.0, 0.8, [-1, 1], null, 'solo-pruebas-v1');
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertRedirect();
        $this->assertTrue($intento->fresh()->calculo_pendiente);
        $this->assertNull($intento->fresh()->id_preg_actual);
    }

    public function test_default_motor_is_unavailable_and_snapshot_cannot_be_replaced(): void
    {
        $this->app->instance(MotorCat::class, new MotorCatPendiente);
        $intento = $this->iniciar();
        $this->assertTrue($intento->calculo_pendiente);
        $this->assertNull($intento->version_motor);
        $this->expectException(LogicException::class);
        $intento->update(['snapshot' => []]);
    }

    public function test_database_unique_index_prevents_second_open_attempt(): void
    {
        $intento = $this->iniciar();
        $this->expectException(QueryException::class);
        DB::table('intentos_cat')->insert(['id_instrumento_cat' => $intento->id_instrumento_cat, 'id_post' => $intento->id_post,
            'estado' => 'en_progreso', 'snapshot' => '{}', 'huella_snapshot' => str_repeat('0', 64), 'iniciado_en' => now(), 'vence_en' => now()->addMinute()]);
    }

    public function test_modified_pool_is_rejected_without_creating_an_attempt(): void
    {
        $this->instrumento->preguntas()->detach(16);
        $this->postJson(route('estudiante.cat.iniciar', $this->instrumento->id_instrumento_cat))->assertUnprocessable();
        $this->assertDatabaseCount('intentos_cat', 0);
    }

    public function test_changed_motor_version_or_foreign_item_is_left_pending(): void
    {
        $intento = $this->iniciar();
        $this->motor->interceptar = fn ($c) => new DecisionCat(2, false, 0, 0.8, [-1, 1], null, 'otra-version');
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertRedirect();
        $this->assertTrue($intento->fresh()->calculo_pendiente);
        $this->assertSame('solo-pruebas-v1', $intento->fresh()->version_motor);
        $this->motor->interceptar = fn ($c) => new DecisionCat(99, false, 0, 0.8, [-1, 1], null, 'solo-pruebas-v1');
        $this->post(route('estudiante.cat.recuperar', $intento->id_intento_cat))->assertRedirect();
        $this->assertNull($intento->fresh()->id_preg_actual);
    }

    public function test_response_accepted_before_deadline_survives_a_late_motor_result(): void
    {
        $intento = $this->iniciar();
        $this->motor->interceptar = function ($c) use ($intento) {
            $this->travelTo($intento->vence_en->addSecond());

            return new DecisionCat(2, false, 0, 0.8, [-1, 1], null, 'solo-pruebas-v1');
        };
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertRedirect();
        $this->assertDatabaseCount('respuestas_cat', 1);
        $this->assertSame('vencida', $intento->fresh()->estado);
        $this->assertNull($intento->fresh()->id_preg_actual);
    }

    public function test_obsolete_calculation_cannot_overwrite_a_newer_revision(): void
    {
        $intento = $this->iniciar();
        $this->motor->interceptar = function ($c) use ($intento) {
            $this->motor->interceptar = null;
            // Simula otra petición que recupera y responde mientras la primera espera al motor.
            $service = app(IntentoCatService::class);
            $service->recuperar($this->postulante, $intento->id_intento_cat);
            $service->responder($this->postulante, $intento->id_intento_cat, $this->respuesta($intento));

            return new DecisionCat(2, false, 0, 0.8, [-1, 1], null, 'solo-pruebas-v1');
        };
        $this->postJson(route('estudiante.cat.responder', $intento->id_intento_cat), $this->respuesta($intento))->assertRedirect();
        $this->assertSame(2, $intento->fresh()->revision);
        $this->assertSame(3, $intento->fresh()->id_preg_actual);
        $this->assertDatabaseCount('respuestas_cat', 2);
    }
}
