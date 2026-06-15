<?php

namespace Tests\Feature\Resultados;

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
        $this->administrator = User::where('email', 'admin@intelecta.test')->firstOrFail();
        $this->student = User::where('email', 'estudiante@intelecta.test')->firstOrFail();
        $this->postulante = Postulante::create([
            'nombres_post' => 'María Elena',
            'apellidos_post' => 'Quispe Choque',
            'email_post' => $this->student->email,
            'gestion_post' => 2026,
            'estado_post' => 'activo',
        ]);

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
