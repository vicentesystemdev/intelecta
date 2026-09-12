<?php

namespace Tests\Feature\Resultados;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Evaluaciones\Models\Alternativa;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Models\User;
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
