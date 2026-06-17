<?php

namespace Tests\Feature\Evaluaciones;

use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EvaluacionesModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private AreaConocimiento $area;

    private Materia $materia;

    private Tema $tema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->administrator = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $this->materia = Materia::create([
            'nombre_mat' => 'Matematica',
            'codigo_mat' => 'MAT',
            'estado_mat' => 'activo',
        ]);
        $this->area = AreaConocimiento::create([
            'id_mat' => $this->materia->id_mat,
            'nombre_area' => 'Álgebra',
            'descripcion_area' => 'Estructuras y ecuaciones algebraicas.',
            'estado_area' => 'activo',
        ]);
        $this->tema = Tema::create([
            'id_area' => $this->area->id_area,
            'nombre_tem' => 'Ecuaciones lineales',
            'nivel_tem' => 'intermedio',
            'estado_tem' => 'activo',
        ]);
    }

    public function test_administrator_can_list_areas(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('areas-conocimiento.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evaluaciones/Areas/Index')
                ->has('areas.data', 1)
                ->where('areas.data.0.nombre_area', 'Álgebra')
            );
    }

    public function test_administrator_can_create_area_with_required_materia(): void
    {
        $response = $this->actingAs($this->administrator)
            ->post(route('areas-conocimiento.store'), [
                'id_mat' => $this->materia->id_mat,
                'nombre_area' => 'Geometria',
                'descripcion_area' => 'Figuras, medidas y relaciones espaciales.',
                'estado_area' => 'activo',
            ]);

        $response->assertRedirect(route('areas-conocimiento.index'));
        $this->assertDatabaseHas('areas_conocimiento', [
            'id_mat' => $this->materia->id_mat,
            'nombre_area' => 'Geometria',
        ]);
    }

    public function test_administrator_can_update_area_with_required_materia(): void
    {
        $response = $this->actingAs($this->administrator)
            ->put(route('areas-conocimiento.update', $this->area->id_area), [
                'id_mat' => $this->materia->id_mat,
                'nombre_area' => 'Algebra elemental',
                'descripcion_area' => 'Estructuras algebraicas de ingreso.',
                'estado_area' => 'activo',
            ]);

        $response->assertRedirect(route('areas-conocimiento.index'));
        $this->assertDatabaseHas('areas_conocimiento', [
            'id_area' => $this->area->id_area,
            'id_mat' => $this->materia->id_mat,
            'nombre_area' => 'Algebra elemental',
        ]);
    }

    public function test_administrator_can_list_temas(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('temas.index', ['id_area' => $this->area->id_area]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evaluaciones/Temas/Index')
                ->has('temas.data', 1)
                ->where('temas.data.0.area.nombre_area', 'Álgebra')
            );
    }

    public function test_administrator_can_list_questions(): void
    {
        $pregunta = $this->createQuestion();

        $this->actingAs($this->administrator)
            ->get(route('preguntas.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evaluaciones/Preguntas/Index')
                ->has('preguntas.data', 1)
                ->where('preguntas.data.0.id_preg', $pregunta->id_preg)
                ->where('preguntas.data.0.tema.area.nombre_area', 'Álgebra')
            );
    }

    public function test_administrator_can_create_question_with_five_alternatives(): void
    {
        $response = $this->actingAs($this->administrator)
            ->post(route('preguntas.store'), $this->validQuestionData());

        $pregunta = Pregunta::where('enunciado_preg', $this->validQuestionData()['enunciado_preg'])->firstOrFail();

        $response->assertRedirect(route('preguntas.index'));
        $this->assertCount(5, $pregunta->alternativas);
        $this->assertSame(1, $pregunta->alternativas->where('es_correcta_alt', true)->count());
    }

    public function test_question_without_correct_alternative_is_rejected(): void
    {
        $data = $this->validQuestionData();
        $data['alternativas'] = collect($data['alternativas'])
            ->map(fn (array $item) => [...$item, 'es_correcta_alt' => false])
            ->all();

        $this->actingAs($this->administrator)
            ->post(route('preguntas.store'), $data)
            ->assertSessionHasErrors('alternativas');

        $this->assertDatabaseMissing('preguntas', ['enunciado_preg' => $data['enunciado_preg']]);
    }

    public function test_multiple_choice_question_without_five_alternatives_is_rejected(): void
    {
        $data = $this->validQuestionData();
        array_pop($data['alternativas']);

        $this->actingAs($this->administrator)
            ->post(route('preguntas.store'), $data)
            ->assertSessionHasErrors('alternativas');
    }

    public function test_administrator_can_list_templates(): void
    {
        $plantilla = PlantillaEvaluacion::create([
            'nombre_plan' => 'Diagnóstico de Álgebra',
            'duracion_minutos_plan' => 60,
            'dificultad_plan' => 'media',
            'estado_plan' => 'activa',
        ]);

        $this->actingAs($this->administrator)
            ->get(route('plantillas-evaluacion.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Evaluaciones/Plantillas/Index')
                ->has('plantillas.data', 1)
                ->where('plantillas.data.0.id_plan', $plantilla->id_plan)
            );
    }

    public function test_administrator_can_create_template_with_questions(): void
    {
        $questions = collect([$this->createQuestion('Pregunta uno'), $this->createQuestion('Pregunta dos')]);

        $response = $this->actingAs($this->administrator)
            ->post(route('plantillas-evaluacion.store'), [
                'nombre_plan' => 'Evaluación institucional de ingreso',
                'descripcion_plan' => 'Instrumento de valoración académica.',
                'objetivo_plan' => 'Identificar el nivel inicial.',
                'duracion_minutos_plan' => 60,
                'dificultad_plan' => 'media',
                'estado_plan' => 'activa',
                'preguntas' => $questions->values()->map(fn (Pregunta $pregunta, int $index) => [
                    'id_preg' => $pregunta->id_preg,
                    'orden_pp' => $index + 1,
                    'puntaje_pp' => 50,
                ])->all(),
            ]);

        $plantilla = PlantillaEvaluacion::where('nombre_plan', 'Evaluación institucional de ingreso')->firstOrFail();

        $response->assertRedirect(route('plantillas-evaluacion.index'));
        $this->assertCount(2, $plantilla->preguntas);
    }

    public function test_template_pivot_scores_total_one_hundred_points(): void
    {
        $questions = collect([$this->createQuestion('Pregunta A'), $this->createQuestion('Pregunta B')]);
        $plantilla = PlantillaEvaluacion::create([
            'nombre_plan' => 'Nivelación matemática',
            'estado_plan' => 'activa',
        ]);
        $plantilla->preguntas()->sync([
            $questions[0]->id_preg => ['orden_pp' => 1, 'puntaje_pp' => 35],
            $questions[1]->id_preg => ['orden_pp' => 2, 'puntaje_pp' => 65],
        ]);

        $this->assertSame(100.0, (float) $plantilla->preguntas->sum('pivot.puntaje_pp'));
    }

    public function test_permissions_allow_teacher_and_reject_student(): void
    {
        $teacher = User::where('email', RolesAndUsersSeeder::TEACHER_EMAIL)->firstOrFail();
        $student = User::where('email', RolesAndUsersSeeder::STUDENT_EMAIL)->firstOrFail();

        $this->actingAs($teacher)->get(route('preguntas.create'))->assertOk();
        $this->actingAs($student)->get(route('preguntas.index'))->assertForbidden();
        $this->actingAs($student)->get(route('plantillas-evaluacion.index'))->assertForbidden();
    }

    private function createQuestion(string $enunciado = 'Si 2x + 4 = 12, ¿cuál es el valor de x?'): Pregunta
    {
        return Pregunta::create([
            'id_tem' => $this->tema->id_tem,
            'enunciado_preg' => $enunciado,
            'tipo_preg' => 'opcion_multiple',
            'dificultad_preg' => 'basica',
            'puntaje_preg' => 1,
            'estado_preg' => 'activo',
        ]);
    }

    private function validQuestionData(): array
    {
        return [
            'id_tem' => $this->tema->id_tem,
            'enunciado_preg' => 'Si 3x - 5 = 16, ¿cuál es el valor de x?',
            'tipo_preg' => 'opcion_multiple',
            'dificultad_preg' => 'basica',
            'puntaje_preg' => 1,
            'explicacion_preg' => 'Se suma 5 a ambos miembros y luego se divide entre 3.',
            'estado_preg' => 'activo',
            'alternativas' => collect(['5', '6', '7', '8', '9'])->map(
                fn (string $text, int $index) => [
                    'texto_alt' => $text,
                    'letra_alt' => chr(65 + $index),
                    'es_correcta_alt' => $index === 2,
                    'orden_alt' => $index + 1,
                    'estado_alt' => 'activo',
                ]
            )->all(),
        ];
    }
}
