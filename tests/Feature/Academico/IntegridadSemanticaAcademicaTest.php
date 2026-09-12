<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegridadSemanticaAcademicaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ProgramaAcademico $programaA;

    private ProgramaAcademico $programaB;

    private GrupoAcademico $grupoA;

    private GrupoAcademico $grupoB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->admin = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $this->programaA = $this->programa('Programa A', 'PA');
        $this->programaB = $this->programa('Programa B', 'PB');
        $this->grupoA = $this->grupo($this->programaA, 'Grupo A', 'GA', 1);
        $this->grupoB = $this->grupo($this->programaB, 'Grupo B', 'GB', 2);
    }

    public function test_rejects_existing_but_incompatible_or_inactive_academic_contexts(): void
    {
        $postulante = Postulante::factory()->create();

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.inscripciones.store'),
            $this->inscripcionPayload($postulante, $this->programaA, $this->grupoB),
        )->assertUnprocessable()->assertJsonValidationErrors('id_grupo');

        $this->grupoA->update(['estado_grupo' => 'inactivo']);
        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.inscripciones.store'),
            $this->inscripcionPayload($postulante, $this->programaA, $this->grupoA),
        )->assertUnprocessable()->assertJsonValidationErrors('id_grupo');

        $this->assertDatabaseCount('inscripciones_academicas', 0);
    }

    public function test_valid_enrollment_consumes_capacity_and_duplicate_or_full_group_is_rejected(): void
    {
        $primero = Postulante::factory()->create();
        $segundo = Postulante::factory()->create();
        $payload = $this->inscripcionPayload($primero, $this->programaA, $this->grupoA);

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.inscripciones.store'),
            $payload,
        )->assertRedirect();

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.inscripciones.store'),
            $payload,
        )->assertUnprocessable()->assertJsonValidationErrors('id_post');

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.inscripciones.store'),
            $this->inscripcionPayload($segundo, $this->programaA, $this->grupoA),
        )->assertUnprocessable()->assertJsonValidationErrors('id_grupo');

        $this->assertDatabaseCount('inscripciones_academicas', 1);
    }

    public function test_tutor_must_be_operational_and_active_assignments_cannot_overlap(): void
    {
        $inactivo = TutorAcademico::factory()->withPersonal()->create(['estado_tutor' => 'inactivo']);
        $archivado = TutorAcademico::factory()->withPersonal()->create(['estado_tutor' => 'activo']);
        $archivado->delete();
        $activo = TutorAcademico::factory()->withPersonal()->create(['estado_tutor' => 'activo']);
        $inicio = today()->addDay();

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.asignacion-tutores.store'),
            $this->asignacionPayload($inactivo, $inicio->toDateString(), $inicio->copy()->addDays(5)->toDateString()),
        )->assertUnprocessable()->assertJsonValidationErrors('id_tutor');

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.asignacion-tutores.store'),
            $this->asignacionPayload($archivado, $inicio->toDateString(), $inicio->copy()->addDays(5)->toDateString()),
        )->assertUnprocessable()->assertJsonValidationErrors('id_tutor');

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.asignacion-tutores.store'),
            $this->asignacionPayload($activo, $inicio->toDateString(), $inicio->copy()->addDays(5)->toDateString()),
        )->assertRedirect();

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.asignacion-tutores.store'),
            $this->asignacionPayload($activo, $inicio->copy()->addDays(3)->toDateString(), $inicio->copy()->addDays(8)->toDateString()),
        )->assertUnprocessable()->assertJsonValidationErrors('id_tutor');

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.asignacion-tutores.store'),
            $this->asignacionPayload($activo, $inicio->copy()->addDays(6)->toDateString(), $inicio->copy()->addDays(10)->toDateString()),
        )->assertRedirect();

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.asignacion-tutores.store'),
            $this->asignacionPayload($activo, $inicio->copy()->addDays(20)->toDateString(), $inicio->copy()->addDays(19)->toDateString()),
        )->assertUnprocessable()->assertJsonValidationErrors('fecha_fin_asig');

        $this->assertDatabaseCount('asignaciones_tutores', 2);
    }

    public function test_matricula_derives_identity_and_rejects_forged_or_inactive_enrollment(): void
    {
        $titular = Postulante::factory()->create();
        $ajeno = Postulante::factory()->create();
        $inscripcion = InscripcionAcademica::create([
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $this->grupoB->id_grupo,
            'id_post' => $titular->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);

        $payload = [
            'id_insc' => $inscripcion->id_insc,
            'monto_matricula_mat' => 350,
            'estado_matricula_mat' => 'activa',
        ];
        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            [...$payload, 'id_post' => $ajeno->id_post],
        )->assertUnprocessable()->assertJsonValidationErrors('id_post');

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            $payload,
        )->assertRedirect();

        $this->assertDatabaseHas('matriculas_academicas', [
            'id_insc' => $inscripcion->id_insc,
            'id_post' => $titular->id_post,
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $this->grupoB->id_grupo,
        ]);

        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            $payload,
        )->assertUnprocessable()->assertJsonValidationErrors('id_insc');

        $inactiva = InscripcionAcademica::create([
            'id_prog' => $this->programaA->id_prog,
            'id_grupo' => null,
            'id_post' => $ajeno->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'inactivo',
        ]);
        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            [...$payload, 'id_insc' => $inactiva->id_insc],
        )->assertUnprocessable()->assertJsonValidationErrors('id_insc');
    }

    public function test_simulation_template_and_question_taxonomy_reject_crossed_ids(): void
    {
        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.simulacros.store'),
            [
                'id_prog' => $this->programaA->id_prog,
                'id_grupo' => $this->grupoB->id_grupo,
                'titulo_sim' => 'Cruce inválido',
                'estado_sim' => 'programado',
            ],
        )->assertUnprocessable()->assertJsonValidationErrors('id_grupo');

        $materiaA = Materia::create(['codigo_mat' => 'MA', 'nombre_mat' => 'Materia A', 'estado_mat' => 'activo']);
        $materiaB = Materia::create(['codigo_mat' => 'MB', 'nombre_mat' => 'Materia B', 'estado_mat' => 'activo']);
        $areaB = AreaConocimiento::create(['id_mat' => $materiaB->id_mat, 'nombre_area' => 'Área B', 'estado_area' => 'activo']);
        $temaB = Tema::create(['id_area' => $areaB->id_area, 'nombre_tem' => 'Tema B', 'estado_tem' => 'activo']);

        $this->actingAs($this->admin)->postJson(route('preguntas.store'), [
            'id_mat' => $materiaA->id_mat,
            'id_area' => $areaB->id_area,
            'id_tem' => $temaB->id_tem,
            'enunciado_preg' => 'Pregunta con clasificación manipulada',
            'tipo_preg' => 'verdadero_falso',
            'puntaje_preg' => 1,
            'estado_preg' => 'activo',
            'alternativas' => $this->alternativasVerdaderoFalso(),
        ])->assertUnprocessable()->assertJsonValidationErrors('id_area');

        $inactiva = Pregunta::create([
            'enunciado_preg' => 'Pregunta inactiva',
            'tipo_preg' => 'respuesta_corta',
            'puntaje_preg' => 1,
            'estado_preg' => 'inactivo',
        ]);
        $this->actingAs($this->admin)->postJson(route('plantillas-evaluacion.store'), [
            'nombre_plan' => 'Plantilla inválida',
            'estado_plan' => 'activa',
            'preguntas' => [[
                'id_preg' => $inactiva->id_preg,
                'orden_pp' => 1,
                'puntaje_pp' => 100,
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('preguntas');
    }

    public function test_group_program_can_only_change_while_group_has_no_academic_dependencies(): void
    {
        $libre = $this->grupo($this->programaA, 'Grupo libre', 'GL', 10);
        $payload = [
            'id_prog' => $this->programaB->id_prog,
            'nombre_grupo' => 'Grupo libre actualizado',
            'codigo_grupo' => 'GL',
            'capacidad_grupo' => 10,
            'estado_grupo' => 'activo',
        ];

        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $libre),
            $payload,
        )->assertRedirect();
        $this->assertSame($this->programaB->id_prog, $libre->fresh()->id_prog);

        $postulante = Postulante::factory()->create();
        InscripcionAcademica::create([
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $libre->id_grupo,
            'id_post' => $postulante->id_post,
            'estado_inscripcion' => 'activo',
        ]);
        $payload['id_prog'] = $this->programaA->id_prog;
        $this->putJson(route('admin.institucional.grupos.update', $libre), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('id_prog');
        $this->assertSame($this->programaB->id_prog, $libre->fresh()->id_prog);

        $conSimulacro = $this->grupo($this->programaA, 'Grupo simulacro', 'GS', 10);
        SimulacroProgramado::create([
            'id_prog' => $this->programaA->id_prog,
            'id_grupo' => $conSimulacro->id_grupo,
            'titulo_sim' => 'Simulacro dependiente',
            'estado_sim' => 'borrador',
        ]);
        $payload['id_prog'] = $this->programaB->id_prog;
        $this->putJson(route('admin.institucional.grupos.update', $conSimulacro), [
            ...$payload,
            'codigo_grupo' => 'GS',
        ])->assertUnprocessable()->assertJsonValidationErrors('id_prog');

        $payload['id_prog'] = $this->programaB->id_prog;
        $payload['aula_grupo'] = 'A-10';
        $this->putJson(route('admin.institucional.grupos.update', $libre), $payload)->assertRedirect();
        $this->assertSame('A-10', $libre->fresh()->aula_grupo);
    }

    public function test_enrollment_context_is_immutable_with_dependencies_but_editable_without_them(): void
    {
        $titular = Postulante::factory()->create();
        $otro = Postulante::factory()->create();
        $inscripcion = InscripcionAcademica::create([
            'id_prog' => $this->programaA->id_prog,
            'id_grupo' => $this->grupoA->id_grupo,
            'id_post' => $titular->id_post,
            'estado_inscripcion' => 'activo',
        ]);
        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            [
                'id_insc' => $inscripcion->id_insc,
                'monto_matricula_mat' => 100,
                'estado_matricula_mat' => 'activa',
            ],
        )->assertRedirect();

        foreach ([
            ['id_post' => $otro->id_post],
            ['id_prog' => $this->programaB->id_prog, 'id_grupo' => $this->grupoB->id_grupo],
            ['id_grupo' => null],
        ] as $cambio) {
            $this->patchJson(route('admin.institucional.inscripciones.update', $inscripcion), [
                'id_prog' => $this->programaA->id_prog,
                'id_grupo' => $this->grupoA->id_grupo,
                'id_post' => $titular->id_post,
                'estado_inscripcion' => 'activo',
                ...$cambio,
            ])->assertUnprocessable();
        }

        $this->assertSame($titular->id_post, $inscripcion->fresh()->id_post);
        $this->assertSame($titular->id_post, $inscripcion->matricula->id_post);
        $this->assertSame($titular->id_post, $inscripcion->habilitacion->id_post);

        $libre = InscripcionAcademica::create([
            'id_prog' => $this->programaA->id_prog,
            'id_grupo' => $this->grupoA->id_grupo,
            'id_post' => $otro->id_post,
            'estado_inscripcion' => 'activo',
        ]);
        $this->patchJson(route('admin.institucional.inscripciones.update', $libre), [
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $this->grupoB->id_grupo,
            'id_post' => $otro->id_post,
            'estado_inscripcion' => 'activo',
        ])->assertRedirect();
        $this->assertSame($this->programaB->id_prog, $libre->fresh()->id_prog);
        $this->assertSame($this->grupoB->id_grupo, $libre->fresh()->id_grupo);
    }

    public function test_matricula_reactivation_revalidates_context_without_blocking_inactive_history(): void
    {
        $postulante = Postulante::factory()->create();
        $inscripcion = InscripcionAcademica::create([
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $this->grupoB->id_grupo,
            'id_post' => $postulante->id_post,
            'estado_inscripcion' => 'activo',
        ]);
        $this->actingAs($this->admin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            [
                'id_insc' => $inscripcion->id_insc,
                'monto_matricula_mat' => 100,
                'estado_matricula_mat' => 'inactiva',
            ],
        )->assertRedirect();
        $matricula = MatriculaAcademica::where('id_insc', $inscripcion->id_insc)->firstOrFail();

        $this->patchJson(route('admin.institucional.matriculas-cuotas.update', $matricula), [
            'id_insc' => $inscripcion->id_insc,
            'monto_matricula_mat' => 120,
            'estado_matricula_mat' => 'inactiva',
        ])->assertRedirect();
        $this->assertSame('120.00', $matricula->fresh()->monto_matricula_mat);

        $inscripcion->update(['estado_inscripcion' => 'inactivo']);
        $this->patchJson(route('admin.institucional.matriculas-cuotas.update', $matricula), [
            'id_insc' => $inscripcion->id_insc,
            'monto_matricula_mat' => 120,
            'estado_matricula_mat' => 'activa',
        ])->assertUnprocessable()->assertJsonValidationErrors('id_insc');
        $this->assertSame('inactiva', $matricula->fresh()->estado_matricula_mat);
        $this->assertFalse($matricula->habilitacion->fresh()->habilitado_evaluaciones_hab);
        $this->assertFalse($matricula->habilitacion->fresh()->habilitado_simulacros_hab);

        $inscripcion->update(['estado_inscripcion' => 'activo']);
        $this->patchJson(route('admin.institucional.matriculas-cuotas.update', $matricula), [
            'id_insc' => $inscripcion->id_insc,
            'monto_matricula_mat' => 120,
            'estado_matricula_mat' => 'becada',
        ])->assertRedirect();
        $this->assertSame('becada', $matricula->fresh()->estado_matricula_mat);
        $this->assertTrue($matricula->habilitacion->fresh()->habilitado_evaluaciones_hab);
    }

    public function test_super_administrator_cannot_bypass_block_seven_semantic_rules(): void
    {
        $superAdmin = User::role('Super Administrador')->firstOrFail();
        $postulante = Postulante::factory()->create();
        $inscripcion = InscripcionAcademica::create([
            'id_prog' => $this->programaA->id_prog,
            'id_grupo' => $this->grupoA->id_grupo,
            'id_post' => $postulante->id_post,
            'estado_inscripcion' => 'activo',
        ]);
        $this->actingAs($superAdmin)->postJson(
            route('admin.institucional.matriculas-cuotas.store'),
            [
                'id_insc' => $inscripcion->id_insc,
                'monto_matricula_mat' => 100,
                'estado_matricula_mat' => 'inactiva',
            ],
        )->assertRedirect();
        $matricula = MatriculaAcademica::where('id_insc', $inscripcion->id_insc)->firstOrFail();

        $this->putJson(route('admin.institucional.grupos.update', $this->grupoA), [
            'id_prog' => $this->programaB->id_prog,
            'nombre_grupo' => $this->grupoA->nombre_grupo,
            'codigo_grupo' => $this->grupoA->codigo_grupo,
            'capacidad_grupo' => 1,
            'estado_grupo' => 'activo',
        ])->assertUnprocessable()->assertJsonValidationErrors('id_prog');
        $this->patchJson(route('admin.institucional.inscripciones.update', $inscripcion), [
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $this->grupoB->id_grupo,
            'id_post' => $postulante->id_post,
            'estado_inscripcion' => 'activo',
        ])->assertUnprocessable();

        $inscripcion->update(['estado_inscripcion' => 'inactivo']);
        $this->patchJson(route('admin.institucional.matriculas-cuotas.update', $matricula), [
            'id_insc' => $inscripcion->id_insc,
            'monto_matricula_mat' => 100,
            'estado_matricula_mat' => 'activa',
        ])->assertUnprocessable()->assertJsonValidationErrors('id_insc');

        $pregunta = Pregunta::create([
            'enunciado_preg' => 'Pregunta archivada para SA',
            'tipo_preg' => 'respuesta_corta',
            'puntaje_preg' => 1,
            'estado_preg' => 'activo',
        ]);
        $plantilla = PlantillaEvaluacion::create([
            'nombre_plan' => 'Plantilla rota para SA',
            'estado_plan' => 'activa',
        ]);
        $plantilla->preguntas()->attach($pregunta->id_preg, [
            'orden_pp' => 1,
            'puntaje_pp' => 100,
        ]);
        $pregunta->delete();
        $postulantePortal = Postulante::factory()->withUser($superAdmin)->create();
        $superAdmin->assignRole('Estudiante');

        $this->postJson(route('estudiante.evaluaciones.iniciar', $plantilla))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('plantilla');
        $this->assertDatabaseMissing('evaluaciones_aplicadas', [
            'id_post' => $postulantePortal->id_post,
            'id_plantilla' => $plantilla->id_plan,
        ]);
    }

    private function programa(string $nombre, string $codigo): ProgramaAcademico
    {
        return ProgramaAcademico::create([
            'nombre_prog' => $nombre,
            'codigo_prog' => $codigo,
            'estado_prog' => 'activo',
        ]);
    }

    private function grupo(
        ProgramaAcademico $programa,
        string $nombre,
        string $codigo,
        int $capacidad,
    ): GrupoAcademico {
        return GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => $nombre,
            'codigo_grupo' => $codigo,
            'capacidad_grupo' => $capacidad,
            'estado_grupo' => 'activo',
        ]);
    }

    private function inscripcionPayload(
        Postulante $postulante,
        ProgramaAcademico $programa,
        GrupoAcademico $grupo,
    ): array {
        return [
            'id_prog' => $programa->id_prog,
            'id_grupo' => $grupo->id_grupo,
            'id_post' => $postulante->id_post,
            'fecha_inscripcion' => today()->toDateString(),
            'estado_inscripcion' => 'activo',
        ];
    }

    private function asignacionPayload(TutorAcademico $tutor, string $inicio, string $fin): array
    {
        return [
            'id_tutor' => $tutor->id_tutor,
            'id_prog' => $this->programaB->id_prog,
            'id_grupo' => $this->grupoB->id_grupo,
            'materia_referencia_asig' => 'Matemática',
            'fecha_inicio_asig' => $inicio,
            'fecha_fin_asig' => $fin,
            'estado_asig' => 'activo',
        ];
    }

    private function alternativasVerdaderoFalso(): array
    {
        return [
            ['letra_alt' => 'A', 'texto_alt' => 'Verdadero', 'es_correcta_alt' => true, 'orden_alt' => 1, 'estado_alt' => 'activo'],
            ['letra_alt' => 'B', 'texto_alt' => 'Falso', 'es_correcta_alt' => false, 'orden_alt' => 2, 'estado_alt' => 'activo'],
        ];
    }
}
