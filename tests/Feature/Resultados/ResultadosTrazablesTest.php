<?php

namespace Tests\Feature\Resultados;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Evaluaciones\Models\Alternativa;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Postulantes\Actions\VincularUsuarioPostulanteAction;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ResultadosTrazablesTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private User $student;

    private Postulante $postulante;

    private PlantillaEvaluacion $plantilla;

    private Pregunta $pregunta;

    private Alternativa $correcta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->administrator = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $this->student = User::where('email', RolesAndUsersSeeder::STUDENT_EMAIL)->firstOrFail();
        $this->postulante = Postulante::create([
            'nombres_post' => 'María Elena',
            'apellidos_post' => 'Quispe Choque',
            'email_post' => 'contacto.evaluaciones@example.com',
            'gestion_post' => 2026,
            'estado_post' => 'activo',
        ]);
        $this->postulante->user()->associate($this->student)->save();

        $materia = Materia::create([
            'codigo_mat' => 'MAT',
            'nombre_mat' => 'Matemática',
            'estado_mat' => 'activo',
        ]);
        $area = AreaConocimiento::create([
            'id_mat' => $materia->id_mat,
            'nombre_area' => 'Álgebra',
            'estado_area' => 'activo',
        ]);
        $tema = Tema::create([
            'id_area' => $area->id_area,
            'nombre_tem' => 'Ecuaciones',
            'estado_tem' => 'activo',
        ]);
        $this->pregunta = Pregunta::create([
            'id_tem' => $tema->id_tem,
            'enunciado_preg' => '¿Cuál es el valor de x si x + 2 = 5?',
            'tipo_preg' => 'opcion_multiple',
            'puntaje_preg' => 10,
            'estado_preg' => 'activo',
        ]);
        $this->correcta = Alternativa::create([
            'id_preg' => $this->pregunta->id_preg,
            'texto_alt' => '3',
            'letra_alt' => 'A',
            'es_correcta_alt' => true,
            'orden_alt' => 1,
            'estado_alt' => 'activo',
        ]);
        Alternativa::create([
            'id_preg' => $this->pregunta->id_preg,
            'texto_alt' => '4',
            'letra_alt' => 'B',
            'es_correcta_alt' => false,
            'orden_alt' => 2,
            'estado_alt' => 'activo',
        ]);
        foreach ([
            ['C', '5'],
            ['D', '2'],
            ['E', '1'],
        ] as $index => [$letra, $texto]) {
            Alternativa::create([
                'id_preg' => $this->pregunta->id_preg,
                'texto_alt' => $texto,
                'letra_alt' => $letra,
                'es_correcta_alt' => false,
                'orden_alt' => $index + 3,
                'estado_alt' => 'activo',
            ]);
        }
        $this->plantilla = PlantillaEvaluacion::create([
            'nombre_plan' => 'Evaluación trazable de Matemática',
            'duracion_minutos_plan' => 30,
            'estado_plan' => 'activa',
        ]);
        $this->plantilla->preguntas()->attach($this->pregunta->id_preg, [
            'orden_pp' => 1,
            'puntaje_pp' => 100,
        ]);
    }

    public function test_administrator_can_list_real_academic_results(): void
    {
        $evaluacion = $this->createOpenEvaluation();
        $evaluacion->update([
            'estado_eval_apl' => 'finalizada',
            'fecha_fin_eval_apl' => now(),
            'puntaje_total_eval_apl' => 100,
            'porcentaje_eval_apl' => 100,
        ]);

        $this->actingAs($this->administrator)
            ->get(route('admin.evaluaciones.resultados'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Modulos/ResultadosSeguimiento')
                ->has('resultados.data', 1)
                ->where('resultados.data.0.id_eval_apl', $evaluacion->id_eval_apl));
    }

    public function test_enabled_postulante_can_start_an_evaluation(): void
    {
        $this->actingAs($this->student)
            ->post(route('estudiante.evaluaciones.iniciar', $this->plantilla))
            ->assertRedirect();

        $this->assertDatabaseHas('evaluaciones_aplicadas', [
            'id_post' => $this->postulante->id_post,
            'id_plantilla' => $this->plantilla->id_plan,
            'estado_eval_apl' => 'en_progreso',
        ]);
    }

    public function test_inactive_enrollment_history_does_not_override_current_enabled_enrollment(): void
    {
        $current = $this->createAdministrativeContext('ACTUAL', true);
        $history = $this->createAdministrativeContext('HISTORIA', false);
        $history->inscripcion->update(['estado_inscripcion' => 'inactivo']);

        $this->actingAs($this->student)->get(route('estudiante.evaluaciones'))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('habilitacionAcademica.habilitado_evaluaciones_hab', true));
        $this->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))->assertRedirect();
        $this->assertDatabaseHas('evaluaciones_aplicadas', ['id_post' => $current->id_post]);
    }

    public function test_simulation_uses_its_enrollment_not_a_restriction_in_another_program(): void
    {
        $current = $this->createAdministrativeContext('SIM-OK', true);
        $this->createAdministrativeContext('OTRO', false);
        $simulation = SimulacroProgramado::create([
            'id_prog' => $current->inscripcion->id_prog,
            'id_grupo' => $current->inscripcion->id_grupo,
            'id_plantilla' => $this->plantilla->id_plan,
            'titulo_sim' => 'Simulacro de contexto habilitado',
            'estado_sim' => 'programado',
        ]);

        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.iniciar', $this->plantilla),
            ['id_sim' => $simulation->id_sim],
        )->assertRedirect();

        $evaluation = EvaluacionAplicada::where('id_sim', $simulation->id_sim)->firstOrFail();
        $this->get(route('estudiante.evaluaciones', ['evaluacion' => $evaluation->id_eval_apl]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('accesoEvaluaciones.permitido', true));
        $this->postJson(route('estudiante.evaluaciones.enviar', $evaluation), [
            'respuestas' => [['id_preg' => $this->pregunta->id_preg, 'id_alt' => $this->correcta->id_alt]],
        ])->assertRedirect();
        $this->assertSame('finalizada', $evaluation->fresh()->estado_eval_apl);
    }

    public function test_expired_temporary_restriction_does_not_block_a_current_habilitation(): void
    {
        $this->createAdministrativeContext('VIGENTE', true);
        HabilitacionAcademica::create([
            'id_post' => $this->postulante->id_post,
            'estado_hab' => 'temporal',
            'fecha_fin_hab' => today()->subDay(),
            'habilitado_evaluaciones_hab' => false,
            'habilitado_simulacros_hab' => false,
        ]);
        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.iniciar', $this->plantilla),
        )->assertRedirect();
    }

    public function test_expired_legacy_record_without_a_current_replacement_does_not_erase_a_debt_restriction(): void
    {
        $habilitation = $this->createAdministrativeContext('LEG-DEUDA', true);
        $habilitation->matricula->update(['fecha_matricula_mat' => today()->subMonths(2)]);
        $this->actingAs($this->administrator)->postJson(route('admin.institucional.cuotas.store'), [
            'id_mat' => $habilitation->id_mat,
            'nro_cuota' => 1,
            'monto_cuota' => 120,
            'estado_cuota' => 'vencida',
        ])->assertRedirect();
        $habilitation->refresh()->update(['fecha_fin_hab' => today()->subDay()]);

        $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))
            ->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        $this->assertDatabaseCount('evaluaciones_aplicadas', 0);
        $this->assertSame('vencida', $habilitation->matricula->cuotas()->firstOrFail()->estado_cuota);
    }

    public function test_explicit_identity_link_restores_enabled_access_but_preserves_overdue_restrictions(): void
    {
        $habilitation = $this->createAdministrativeContext('IDENTIDAD-LEGACY', true);
        $habilitation->matricula->update(['fecha_matricula_mat' => today()->subMonths(2)]);
        $habilitation->update(['fecha_fin_hab' => today()->subMonth()]);
        $this->postulante->user()->dissociate()->save();
        $this->postulante->update(['email_post' => $this->student->email]);
        $this->actingAs($this->student)->getJson(route('estudiante.evaluaciones'))->assertForbidden();
        $this->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))->assertForbidden();

        $responsible = User::role('Super Administrador')->firstOrFail();
        app(VincularUsuarioPostulanteAction::class)->execute(
            $this->student->id,
            $this->postulante->id_post,
            $responsible,
            'Confirmación explícita de identidad en fixture aislada.',
        );
        $this->actingAs($this->student->fresh())->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))->assertRedirect();
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'vincular_identidad', 'entidad_id' => (string) $this->postulante->id_post]);

        $this->actingAs($this->administrator)->postJson(route('admin.institucional.cuotas.store'), [
            'id_mat' => $habilitation->id_mat,
            'nro_cuota' => 1,
            'monto_cuota' => 120,
            'estado_cuota' => 'vencida',
        ])->assertRedirect();
        $this->actingAs($this->student->fresh())->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))
            ->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
    }

    public function test_installment_states_keep_the_existing_access_rules_and_payment_updates_are_immediate(): void
    {
        $habilitation = $this->createAdministrativeContext('CUOTAS', true);
        $payload = ['id_mat' => $habilitation->id_mat, 'nro_cuota' => 1, 'monto_cuota' => 120];
        $this->actingAs($this->administrator)->postJson(route('admin.institucional.cuotas.store'), [
            ...$payload, 'estado_cuota' => 'pendiente',
        ])->assertRedirect();
        $installment = $habilitation->matricula->cuotas()->firstOrFail();

        foreach (['pendiente' => true, 'vencida' => false, 'pagada' => true, 'becada' => true, 'exenta' => true] as $state => $allowed) {
            $this->actingAs($this->administrator)->patchJson(route('admin.institucional.cuotas.update', $installment), [
                ...$payload,
                'estado_cuota' => $state,
                'fecha_pago_cuota' => $state === 'pagada' ? today()->toDateString() : null,
            ])->assertRedirect();
            $this->assertSame($allowed, $habilitation->fresh()->habilitado_evaluaciones_hab, $state);
            $this->assertSame('120.00', $installment->fresh()->monto_cuota);
            $this->actingAs($this->student)->get(route('estudiante.evaluaciones'))
                ->assertOk()->assertInertia(fn (Assert $page) => $page->where('accesoEvaluaciones.permitido', $allowed));
            $response = $this->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla), [
                'habilitado_evaluaciones_hab' => true,
                'estado_hab' => 'habilitado',
                'historica' => true,
            ]);
            $allowed ? $response->assertRedirect() : $response->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        }
    }

    public function test_benefits_and_inactive_registration_retain_their_legitimate_restrictions(): void
    {
        $habilitation = $this->createAdministrativeContext('BENEFICIOS', true);
        $this->actingAs($this->administrator)->postJson(route('admin.institucional.cuotas.store'), [
            'id_mat' => $habilitation->id_mat,
            'nro_cuota' => 1,
            'monto_cuota' => 120,
            'estado_cuota' => 'vencida',
        ])->assertRedirect();

        foreach (['becada' => true, 'exenta' => true, 'inactiva' => false, 'activa' => false] as $state => $allowed) {
            $this->actingAs($this->administrator)->patchJson(route('admin.institucional.matriculas-cuotas.update', $habilitation->id_mat), [
                'id_insc' => $habilitation->id_insc,
                'monto_matricula_mat' => 100,
                'estado_matricula_mat' => $state,
            ])->assertRedirect();
            $this->assertSame($allowed, $habilitation->fresh()->habilitado_evaluaciones_hab, $state);
            $response = $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla));
            $allowed ? $response->assertRedirect() : $response->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        }
    }

    public function test_admin_can_restrict_and_reenable_start_and_submission_without_touching_payments(): void
    {
        $habilitation = $this->createAdministrativeContext('AJUSTE', true);
        $evaluation = $this->createOpenEvaluation();
        $payload = [
            'estado_hab' => 'restringido',
            'habilitado_evaluaciones_hab' => false,
            'habilitado_simulacros_hab' => false,
            'habilitado_reportes_hab' => true,
        ];
        $this->actingAs($this->administrator)->patchJson(route('admin.institucional.habilitacion.update', $habilitation), $payload)->assertRedirect();
        $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))
            ->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        $answers = ['respuestas' => [['id_preg' => $this->pregunta->id_preg, 'id_alt' => $this->correcta->id_alt]]];
        $this->postJson(route('estudiante.evaluaciones.enviar', $evaluation), $answers)
            ->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        $this->assertDatabaseCount('respuestas_evaluacion', 0);

        $this->actingAs($this->administrator)->patchJson(route('admin.institucional.habilitacion.update', $habilitation), [
            ...$payload, 'estado_hab' => 'habilitado', 'habilitado_evaluaciones_hab' => true, 'habilitado_simulacros_hab' => true,
        ])->assertRedirect();
        $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))->assertRedirect();
        $this->postJson(route('estudiante.evaluaciones.enviar', $evaluation), $answers)->assertRedirect();
        $this->assertSame('finalizada', $evaluation->fresh()->estado_eval_apl);
        $this->assertDatabaseCount('cuotas_academicas', 0);
    }

    public function test_simulation_restriction_cannot_be_overridden_by_an_enabled_unrelated_enrollment(): void
    {
        $blocked = $this->createAdministrativeContext('SIM-NO', false);
        $this->createAdministrativeContext('OTRO-SI', true);
        $simulation = SimulacroProgramado::create([
            'id_prog' => $blocked->inscripcion->id_prog,
            'id_plantilla' => $this->plantilla->id_plan,
            'titulo_sim' => 'Simulacro restringido',
            'estado_sim' => 'programado',
        ]);
        $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla), [
            'id_sim' => $simulation->id_sim,
            'habilitado_evaluaciones_hab' => true,
            'historica' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        $this->assertDatabaseCount('evaluaciones_aplicadas', 0);
    }

    public function test_habilitation_date_limits_use_bolivian_days_and_include_the_end_date(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-20 02:00:00', 'UTC'));
        $this->createAdministrativeContext('FECHAS', true);
        $restriction = HabilitacionAcademica::create([
            'id_post' => $this->postulante->id_post,
            'fecha_inicio_hab' => '2026-10-19',
            'fecha_fin_hab' => '2026-10-19',
            'estado_hab' => 'temporal',
            'habilitado_evaluaciones_hab' => false,
        ]);
        $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))
            ->assertUnprocessable()->assertJsonValidationErrors('habilitacion');
        $this->travelTo(CarbonImmutable::parse('2026-10-20 04:00:00', 'UTC'));
        $this->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))->assertRedirect();
        $restriction->update(['fecha_inicio_hab' => '2026-10-21', 'fecha_fin_hab' => null]);
        $this->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))->assertRedirect();
    }

    private function createAdministrativeContext(string $code, bool $enabled): HabilitacionAcademica
    {
        $program = ProgramaAcademico::create([
            'nombre_prog' => 'Programa '.$code,
            'codigo_prog' => $code,
            'estado_prog' => 'activo',
        ]);
        $enrollment = InscripcionAcademica::create([
            'id_post' => $this->postulante->id_post,
            'id_prog' => $program->id_prog,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);
        $registration = MatriculaAcademica::create([
            'id_insc' => $enrollment->id_insc,
            'id_post' => $this->postulante->id_post,
            'id_prog' => $program->id_prog,
            'fecha_matricula_mat' => today(),
            'estado_matricula_mat' => 'activa',
        ]);

        return HabilitacionAcademica::create([
            'id_post' => $this->postulante->id_post,
            'id_insc' => $enrollment->id_insc,
            'id_mat' => $registration->id_mat,
            'estado_hab' => $enabled ? 'habilitado' : 'restringido',
            'habilitado_evaluaciones_hab' => $enabled,
            'habilitado_simulacros_hab' => $enabled,
            'habilitado_reportes_hab' => true,
        ]);
    }

    public function test_finalized_evaluation_persists_answers_and_calculates_score(): void
    {
        $evaluacion = $this->createOpenEvaluation();

        $this->actingAs($this->student)
            ->post(route('estudiante.evaluaciones.enviar', $evaluacion), [
                'respuestas' => [[
                    'id_preg' => $this->pregunta->id_preg,
                    'id_alt' => $this->correcta->id_alt,
                    'tiempo_segundos' => 30,
                ]],
                'tiempo_total_segundos' => 30,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('respuestas_evaluacion', [
            'id_eval_apl' => $evaluacion->id_eval_apl,
            'id_preg' => $this->pregunta->id_preg,
            'es_correcta_resp' => true,
            'puntaje_obtenido_resp' => 100,
        ]);
        $this->assertDatabaseHas('evaluaciones_aplicadas', [
            'id_eval_apl' => $evaluacion->id_eval_apl,
            'estado_eval_apl' => 'finalizada',
            'porcentaje_eval_apl' => 100,
        ]);
    }

    public function test_question_outside_template_is_rejected(): void
    {
        $evaluacion = $this->createOpenEvaluation();
        $otraPregunta = Pregunta::create([
            'enunciado_preg' => 'Pregunta ajena a la plantilla',
            'tipo_preg' => 'opcion_multiple',
            'puntaje_preg' => 10,
            'estado_preg' => 'activo',
        ]);

        $this->actingAs($this->student)
            ->post(route('estudiante.evaluaciones.enviar', $evaluacion), [
                'respuestas' => [[
                    'id_preg' => $otraPregunta->id_preg,
                ]],
            ])
            ->assertSessionHasErrors('respuestas');

        $this->assertSame('en_progreso', $evaluacion->refresh()->estado_eval_apl);
    }

    public function test_alternative_from_another_question_or_inactive_is_rejected(): void
    {
        $evaluacion = $this->createOpenEvaluation();
        $otraPregunta = Pregunta::create([
            'enunciado_preg' => 'Pregunta propietaria de otra alternativa',
            'tipo_preg' => 'opcion_multiple',
            'puntaje_preg' => 10,
            'estado_preg' => 'activo',
        ]);
        $ajena = Alternativa::create([
            'id_preg' => $otraPregunta->id_preg,
            'texto_alt' => 'Alternativa ajena',
            'letra_alt' => 'A',
            'es_correcta_alt' => true,
            'orden_alt' => 1,
            'estado_alt' => 'activo',
        ]);

        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.enviar', $evaluacion),
            ['respuestas' => [['id_preg' => $this->pregunta->id_preg, 'id_alt' => $ajena->id_alt]]],
        )->assertUnprocessable()->assertJsonValidationErrors('respuestas');

        $this->correcta->update(['estado_alt' => 'inactivo']);
        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.enviar', $evaluacion),
            ['respuestas' => [['id_preg' => $this->pregunta->id_preg, 'id_alt' => $this->correcta->id_alt]]],
        )->assertUnprocessable()->assertJsonValidationErrors('respuestas');

        $this->assertDatabaseCount('respuestas_evaluacion', 0);
    }

    public function test_student_cannot_read_or_submit_another_students_evaluation(): void
    {
        $otherUser = User::factory()->create()->assignRole('Estudiante');
        $otherPostulante = Postulante::factory()->withUser($otherUser)->create(['email_post' => $this->student->email]);
        $evaluation = $this->createOpenEvaluation();
        $evaluation->update(['id_post' => $otherPostulante->id_post]);

        $this->actingAs($this->student)->get(route('estudiante.evaluaciones', ['evaluacion' => $evaluation->id_eval_apl]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('evaluacionActiva', null)->has('historial', 0));
        $this->postJson(route('estudiante.evaluaciones.enviar', $evaluation), [
            'id_post' => $otherPostulante->id_post,
            'respuestas' => [['id_preg' => $this->pregunta->id_preg, 'id_alt' => $this->correcta->id_alt]],
        ])->assertNotFound();
        $this->assertSame('en_progreso', $evaluation->fresh()->estado_eval_apl);
        $this->assertDatabaseCount('respuestas_evaluacion', 0);
        $evaluation->update(['estado_eval_apl' => 'finalizada', 'fecha_fin_eval_apl' => now()]);
        $this->get(route('estudiante.evaluaciones', ['resultado' => $evaluation->id_eval_apl]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('resultado', null)->has('historial', 0));
    }

    public function test_start_ignores_a_forged_postulante_id(): void
    {
        $other = Postulante::factory()->create(['email_post' => $this->student->email]);
        $this->actingAs($this->student)->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla), ['id_post' => $other->id_post])
            ->assertRedirect();
        $this->assertDatabaseHas('evaluaciones_aplicadas', ['id_post' => $this->postulante->id_post]);
        $this->assertDatabaseMissing('evaluaciones_aplicadas', ['id_post' => $other->id_post]);
    }

    public function test_simulation_attempt_requires_students_exact_active_context(): void
    {
        $programa = ProgramaAcademico::create([
            'nombre_prog' => 'Programa de simulacro',
            'codigo_prog' => 'SIM-P',
            'estado_prog' => 'activo',
        ]);
        $grupoPropio = GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => 'Grupo propio',
            'codigo_grupo' => 'SIM-A',
            'capacidad_grupo' => 10,
            'estado_grupo' => 'activo',
        ]);
        $grupoAjeno = GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => 'Grupo ajeno',
            'codigo_grupo' => 'SIM-B',
            'capacidad_grupo' => 10,
            'estado_grupo' => 'activo',
        ]);
        InscripcionAcademica::create([
            'id_prog' => $programa->id_prog,
            'id_grupo' => $grupoPropio->id_grupo,
            'id_post' => $this->postulante->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);
        $ajeno = SimulacroProgramado::create([
            'id_prog' => $programa->id_prog,
            'id_grupo' => $grupoAjeno->id_grupo,
            'id_plantilla' => $this->plantilla->id_plan,
            'titulo_sim' => 'Simulacro ajeno',
            'estado_sim' => 'programado',
        ]);

        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.iniciar', $this->plantilla),
            ['id_sim' => $ajeno->id_sim],
        )->assertUnprocessable()->assertJsonValidationErrors('id_sim');

        $propio = SimulacroProgramado::create([
            'id_prog' => $programa->id_prog,
            'id_grupo' => $grupoPropio->id_grupo,
            'id_plantilla' => $this->plantilla->id_plan,
            'titulo_sim' => 'Simulacro propio',
            'estado_sim' => 'programado',
        ]);

        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.iniciar', $this->plantilla),
            ['id_sim' => $propio->id_sim],
        )->assertRedirect();

        $this->assertDatabaseHas('evaluaciones_aplicadas', [
            'id_post' => $this->postulante->id_post,
            'id_plantilla' => $this->plantilla->id_plan,
            'id_sim' => $propio->id_sim,
            'estado_eval_apl' => 'en_progreso',
        ]);
    }

    public function test_archived_question_makes_template_unavailable_for_new_attempt(): void
    {
        $segunda = Pregunta::create([
            'enunciado_preg' => 'Pregunta que será archivada antes de iniciar',
            'tipo_preg' => 'respuesta_corta',
            'puntaje_preg' => 1,
            'estado_preg' => 'activo',
        ]);
        $this->plantilla->preguntas()->updateExistingPivot($this->pregunta->id_preg, ['puntaje_pp' => 50]);
        $this->plantilla->preguntas()->attach($segunda->id_preg, [
            'orden_pp' => 2,
            'puntaje_pp' => 50,
        ]);
        $segunda->delete();

        $this->actingAs($this->student)
            ->postJson(route('estudiante.evaluaciones.iniciar', $this->plantilla))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('plantilla');

        $this->assertDatabaseCount('evaluaciones_aplicadas', 0);
        $this->assertDatabaseHas('plantilla_preguntas', [
            'id_plan' => $this->plantilla->id_plan,
            'id_preg' => $segunda->id_preg,
            'puntaje_pp' => 50,
        ]);
    }

    public function test_attempt_cannot_finish_if_a_required_question_was_archived(): void
    {
        $segunda = Pregunta::create([
            'enunciado_preg' => 'Pregunta que será archivada durante el intento',
            'tipo_preg' => 'respuesta_corta',
            'puntaje_preg' => 1,
            'estado_preg' => 'activo',
        ]);
        $this->plantilla->preguntas()->updateExistingPivot($this->pregunta->id_preg, ['puntaje_pp' => 50]);
        $this->plantilla->preguntas()->attach($segunda->id_preg, [
            'orden_pp' => 2,
            'puntaje_pp' => 50,
        ]);
        $evaluacion = $this->createOpenEvaluation();
        $segunda->delete();

        $this->actingAs($this->student)->postJson(
            route('estudiante.evaluaciones.enviar', $evaluacion),
            ['respuestas' => [[
                'id_preg' => $this->pregunta->id_preg,
                'id_alt' => $this->correcta->id_alt,
            ]]],
        )->assertUnprocessable()->assertJsonValidationErrors('evaluacion');

        $this->assertSame('en_progreso', $evaluacion->fresh()->estado_eval_apl);
        $this->assertSame('100.00', $evaluacion->puntaje_maximo_eval_apl);
        $this->assertDatabaseCount('respuestas_evaluacion', 0);
    }

    private function createOpenEvaluation(): EvaluacionAplicada
    {
        return EvaluacionAplicada::create([
            'id_post' => $this->postulante->id_post,
            'id_plantilla' => $this->plantilla->id_plan,
            'codigo_eval_apl' => 'EVA-TEST-'.$this->postulante->id_post,
            'fecha_inicio_eval_apl' => now(),
            'estado_eval_apl' => 'en_progreso',
            'puntaje_maximo_eval_apl' => 100,
        ]);
    }
}
