<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Domains\Seguridad\Support\MatrizRbac;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AmbitoDocenteTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherA;

    private User $teacherB;

    private AsignacionTutor $assignmentA;

    private AsignacionTutor $assignmentB;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (MatrizRbac::CATALOG as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        foreach (MatrizRbac::ROLES as $role) {
            Role::findOrCreate($role, 'web')->syncPermissions(MatrizRbac::forRole($role));
        }

        $programA = ProgramaAcademico::create(['nombre_prog' => 'Programa A', 'codigo_prog' => 'BLOCK6-A']);
        $programB = ProgramaAcademico::create(['nombre_prog' => 'Programa B', 'codigo_prog' => 'BLOCK6-B']);
        $groupA = GrupoAcademico::create(['id_prog' => $programA->id_prog, 'nombre_grupo' => 'Grupo A', 'codigo_grupo' => 'A']);
        $groupB = GrupoAcademico::create(['id_prog' => $programB->id_prog, 'nombre_grupo' => 'Grupo B', 'codigo_grupo' => 'B']);

        [$this->teacherA, $tutorA] = $this->teacherWithTutor('Docente A');
        [$this->teacherB, $tutorB] = $this->teacherWithTutor('Docente B');
        $this->assignmentA = AsignacionTutor::create([
            'id_tutor' => $tutorA->id_tutor,
            'id_prog' => $programA->id_prog,
            'id_grupo' => $groupA->id_grupo,
            'estado_asig' => 'activo',
        ]);
        $this->assignmentB = AsignacionTutor::create([
            'id_tutor' => $tutorB->id_tutor,
            'id_prog' => $programB->id_prog,
            'id_grupo' => $groupB->id_grupo,
            'estado_asig' => 'activo',
        ]);

        $studentA = $this->studentInGroup($groupA, 'Ana', 'Ámbito A');
        $studentB = $this->studentInGroup($groupB, 'Beatriz', 'Ámbito B');
        AsistenciaAcademica::create([
            'id_prog' => $programA->id_prog,
            'id_grupo' => $groupA->id_grupo,
            'id_post' => $studentA->id_post,
            'id_tutor' => $tutorA->id_tutor,
            'fecha_asist' => today()->subDay()->format('Y-m-d'),
            'sesion_asist' => 'Inicial A',
            'estado_asist' => 'presente',
        ]);
        AsistenciaAcademica::create([
            'id_prog' => $programB->id_prog,
            'id_grupo' => $groupB->id_grupo,
            'id_post' => $studentB->id_post,
            'id_tutor' => $tutorB->id_tutor,
            'fecha_asist' => today()->subDay()->format('Y-m-d'),
            'sesion_asist' => 'Inicial B',
            'estado_asist' => 'ausente',
        ]);
        $templateA = PlantillaEvaluacion::create(['nombre_plan' => 'Plantilla A']);
        $templateB = PlantillaEvaluacion::create(['nombre_plan' => 'Plantilla B']);
        $simulationA = SimulacroProgramado::create([
            'id_prog' => $programA->id_prog,
            'id_grupo' => $groupA->id_grupo,
            'id_plantilla' => $templateA->id_plan,
            'titulo_sim' => 'Simulacro A',
            'fecha_sim' => today(),
        ]);
        $simulationB = SimulacroProgramado::create([
            'id_prog' => $programB->id_prog,
            'id_grupo' => $groupB->id_grupo,
            'id_plantilla' => $templateB->id_plan,
            'titulo_sim' => 'Simulacro B',
            'fecha_sim' => today(),
        ]);
        EvaluacionAplicada::create([
            'id_post' => $studentA->id_post,
            'id_plantilla' => $templateA->id_plan,
            'id_sim' => $simulationA->id_sim,
            'codigo_eval_apl' => 'EVAL-A',
            'estado_eval_apl' => 'finalizada',
            'fecha_fin_eval_apl' => now()->subHour(),
            'porcentaje_eval_apl' => 80,
        ]);
        EvaluacionAplicada::create([
            'id_post' => $studentB->id_post,
            'id_plantilla' => $templateB->id_plan,
            'id_sim' => $simulationB->id_sim,
            'codigo_eval_apl' => 'EVAL-B',
            'estado_eval_apl' => 'finalizada',
            'fecha_fin_eval_apl' => now()->subHour(),
            'porcentaje_eval_apl' => 60,
        ]);
    }

    public function test_two_teachers_are_isolated_in_lists_filters_options_details_and_results(): void
    {
        $studentA = $this->activeStudent($this->assignmentA->id_grupo);
        $studentB = $this->activeStudent($this->assignmentB->id_grupo);
        $evaluationA = EvaluacionAplicada::where('id_post', $studentA->id_post)->where('estado_eval_apl', 'finalizada')->firstOrFail();
        $evaluationB = EvaluacionAplicada::where('id_post', $studentB->id_post)->where('estado_eval_apl', 'finalizada')->firstOrFail();

        $this->assertSame('pendiente', $this->teacherA->personalInstitucional->fresh()->estado->value);
        $this->actingAs($this->teacherA)
            ->get(route('admin.institucional.grupos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('grupos.total', 1)
                ->where('grupos.data.0.id_grupo', $this->assignmentA->id_grupo)
                ->where('programas.0.id_prog', $this->assignmentA->id_prog));

        $this->get(route('admin.institucional.grupos.index', ['id_prog' => $this->assignmentB->id_prog]))
            ->assertForbidden();

        $this->get(route('postulantes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('postulantes.total', fn ($total) => $total > 0)
                ->where('postulantes.data', fn ($rows) => collect($rows)->every(
                    fn ($row) => InscripcionAcademica::query()
                        ->where('id_grupo', $this->assignmentA->id_grupo)
                        ->where('estado_inscripcion', 'activo')
                        ->where('id_post', $row['id_post'])
                        ->exists(),
                ))
                ->missing('postulantes.data.0.ci_post')
                ->missing('postulantes.data.0.email_post'));

        $this->get(route('postulantes.index', ['buscar' => $studentB->nombres_post]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('postulantes.total', 0));
        $this->get(route('postulantes.show', $studentA))->assertOk();
        $this->get(route('postulantes.show', $studentB))->assertForbidden();

        $this->get(route('admin.institucional.ficha.index', ['id_grupo' => $this->assignmentA->id_grupo]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('fichas.total', fn ($total) => $total > 0)
                ->where('grupos', fn ($groups) => collect($groups)->pluck('id_grupo')->all() === [$this->assignmentA->id_grupo])
                ->missing('fichas.data.0.ci_post')
                ->missing('fichas.data.0.email_post'));
        $this->get(route('admin.institucional.ficha.index', ['id_grupo' => $this->assignmentB->id_grupo]))
            ->assertForbidden();
        $this->get(route('admin.institucional.ficha.postulante', $studentA))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->missing('postulante.ci_post')
                ->missing('postulante.email_post')
                ->missing('postulante.celular_post')
                ->where('administracion', null));
        $this->get(route('admin.institucional.ficha.postulante', $studentB))->assertForbidden();

        $this->get(route('admin.institucional.asistencia.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('grupos', fn ($groups) => collect($groups)->pluck('id_grupo')->all() === [$this->assignmentA->id_grupo])
                ->where('inscripciones', fn ($rows) => collect($rows)->every(
                    fn ($row) => $row['id_grupo'] === $this->assignmentA->id_grupo,
                ))
                ->where('tutores', fn ($rows) => collect($rows)->pluck('id_tutor')->all() === [$this->assignmentA->id_tutor])
                ->where('asistencias.data', fn ($rows) => collect($rows)->every(
                    fn ($row) => $row['id_grupo'] === $this->assignmentA->id_grupo,
                )));
        $this->get(route('admin.institucional.asistencia.index', ['id_grupo' => $this->assignmentB->id_grupo]))
            ->assertForbidden();

        $this->get(route('admin.evaluaciones.resultados'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('resultados.total', fn ($total) => $total > 0)
                ->where('resultados.data', fn ($rows) => collect($rows)->every(
                    fn ($row) => $row['id_post'] !== $studentB->id_post,
                ))
                ->missing('resultados.data.0.postulante.ci_post'));
        $this->get(route('admin.evaluaciones.resultados', ['buscar' => $studentB->nombres_post]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('resultados.total', 0));

        $this->assertNotSame($evaluationA->id_eval_apl, $evaluationB->id_eval_apl);

        $this->actingAs($this->teacherB)
            ->get(route('admin.institucional.grupos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('grupos.total', 1)
                ->where('grupos.data.0.id_grupo', $this->assignmentB->id_grupo));
        $this->get(route('postulantes.show', $studentA))->assertForbidden();
        $this->get(route('postulantes.show', $studentB))->assertOk();
    }

    public function test_attendance_create_does_not_edit_and_forged_ids_are_rejected(): void
    {
        $studentA = $this->activeStudent($this->assignmentA->id_grupo);
        $studentB = $this->activeStudent($this->assignmentB->id_grupo);
        $payload = [
            'id_prog' => $this->assignmentA->id_prog,
            'id_grupo' => $this->assignmentA->id_grupo,
            'id_post' => $studentA->id_post,
            'id_tutor' => $this->assignmentA->id_tutor,
            'fecha_asist' => today()->format('Y-m-d'),
            'sesion_asist' => 'Bloque 6 - aislamiento',
            'estado_asist' => 'presente',
        ];

        $this->actingAs($this->teacherA)
            ->postJson(route('admin.institucional.asistencia.store'), $payload)
            ->assertRedirect();
        $attendance = AsistenciaAcademica::query()
            ->where('id_grupo', $this->assignmentA->id_grupo)
            ->where('id_post', $studentA->id_post)
            ->where('sesion_asist', $payload['sesion_asist'])
            ->sole();

        $this->postJson(route('admin.institucional.asistencia.store'), [
            ...$payload,
            'estado_asist' => 'ausente',
        ])->assertUnprocessable();
        $this->assertSame('presente', $attendance->fresh()->estado_asist);

        $this->patchJson(route('admin.institucional.asistencia.update', $attendance), [
            ...$payload,
            'estado_asist' => 'justificado',
        ])->assertForbidden();
        $this->assertSame('presente', $attendance->fresh()->estado_asist);

        $this->postJson(route('admin.institucional.asistencia.store'), [
            ...$payload,
            'id_prog' => $this->assignmentB->id_prog,
            'id_grupo' => $this->assignmentB->id_grupo,
            'id_post' => $studentB->id_post,
            'id_tutor' => $this->assignmentB->id_tutor,
        ])->assertForbidden();
        $this->postJson(route('admin.institucional.asistencia.store'), [
            ...$payload,
            'id_post' => $studentB->id_post,
        ])->assertForbidden();
        $this->postJson(route('admin.institucional.asistencia.store'), [
            ...$payload,
            'id_tutor' => $this->assignmentB->id_tutor,
        ])->assertForbidden();

        $this->postJson(route('admin.institucional.asistencia.store-grupo'), [
            'id_prog' => $this->assignmentA->id_prog,
            'id_grupo' => $this->assignmentA->id_grupo,
            'id_tutor' => $this->assignmentA->id_tutor,
            'fecha_asist' => $payload['fecha_asist'],
            'sesion_asist' => $payload['sesion_asist'],
            'registros' => [[
                'id_post' => $studentA->id_post,
                'estado_asist' => 'ausente',
                'observacion_asist' => null,
            ]],
        ])->assertUnprocessable();
        $this->assertSame('presente', $attendance->fresh()->estado_asist);
    }

    public function test_empty_archived_and_multirole_contexts_fail_closed_or_remain_global(): void
    {
        $unassigned = User::factory()->active()->create()->assignRole('Docente');
        $person = PersonalInstitucional::factory()->pending()->create(['user_id' => $unassigned->id]);
        TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);

        $this->actingAs($unassigned)
            ->get(route('admin.institucional.grupos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('grupos.total', 0)
                ->where('programas', []));
        $this->get(route('postulantes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('postulantes.total', 0)
                ->where('opciones.colegios', [])
                ->where('opciones.universidades', [])
                ->where('opciones.carreras', []));

        $this->assignmentA->tutor->delete();
        $this->actingAs($this->teacherA)
            ->get(route('admin.institucional.grupos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('grupos.total', 0));

        $this->teacherA->assignRole('Administrador');
        $this->get(route('admin.institucional.grupos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.roles', ['Administrador', 'Docente'])
                ->where('grupos.total', GrupoAcademico::count()));
    }

    public function test_teacher_cannot_open_global_catalog_consumers_of_postulantes_permission(): void
    {
        $unassigned = User::factory()->active()->create()->assignRole('Docente');

        foreach ([$unassigned, $this->teacherA] as $teacher) {
            $this->actingAs($teacher);
            $this->get(route('admin.colegios'))->assertForbidden();
            $this->get(route('admin.carreras'))->assertForbidden();
        }

        $admin = User::factory()->active()->create()->assignRole('Administrador');
        $this->actingAs($admin);
        $this->get(route('admin.colegios'))->assertOk();
        $this->get(route('admin.carreras'))->assertOk();
    }

    public function test_simulation_context_prevents_cross_group_and_contextless_result_leaks(): void
    {
        $groupA = GrupoAcademico::findOrFail($this->assignmentA->id_grupo);
        $groupB = GrupoAcademico::findOrFail($this->assignmentB->id_grupo);
        $student = $this->studentInGroup($groupA, 'Compartida', 'A y B');
        InscripcionAcademica::create([
            'id_prog' => $groupB->id_prog,
            'id_grupo' => $groupB->id_grupo,
            'id_post' => $student->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);
        $templateB = PlantillaEvaluacion::create(['nombre_plan' => 'Plantilla exclusiva B']);
        $simulationB = SimulacroProgramado::create([
            'id_prog' => $groupB->id_prog,
            'id_grupo' => $groupB->id_grupo,
            'id_plantilla' => $templateB->id_plan,
            'titulo_sim' => 'Simulacro exclusivo B',
            'fecha_sim' => today(),
        ]);
        $foreign = EvaluacionAplicada::create([
            'id_post' => $student->id_post,
            'id_plantilla' => $templateB->id_plan,
            'id_sim' => $simulationB->id_sim,
            'codigo_eval_apl' => 'RESULTADO-B-COMPARTIDA',
            'estado_eval_apl' => 'finalizada',
            'fecha_fin_eval_apl' => now(),
            'porcentaje_eval_apl' => 39,
            'observacion_eval_apl' => 'Observación privada de B',
        ]);
        EvaluacionAplicada::create([
            'id_post' => $student->id_post,
            'id_plantilla' => $templateB->id_plan,
            'codigo_eval_apl' => 'RESULTADO-SIN-CONTEXTO',
            'estado_eval_apl' => 'finalizada',
            'fecha_fin_eval_apl' => now(),
            'porcentaje_eval_apl' => 50,
        ]);

        $this->actingAs($this->teacherA)
            ->get(route('admin.evaluaciones.resultados'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('metricas.total', 1)
                ->where('resultados.data', fn ($rows) => collect($rows)
                    ->every(fn ($row) => $row['id_eval_apl'] !== $foreign->id_eval_apl))
                ->missing('resultados.data.0.observacion_eval_apl'));
        $this->get(route('admin.evaluaciones.resultados', ['id_plantilla' => $templateB->id_plan]))
            ->assertForbidden();
        $this->get(route('admin.institucional.ficha.postulante', $student))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('evaluacionesAplicadas', []));

        $this->actingAs($this->teacherB)
            ->get(route('admin.evaluaciones.resultados', ['id_plantilla' => $templateB->id_plan]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('resultados.total', 1)
                ->where('resultados.data.0.id_eval_apl', $foreign->id_eval_apl)
                ->where('metricas.total', 2)
                ->where('plantillas', fn ($rows) => collect($rows)->contains('id_plan', $templateB->id_plan))
                ->missing('resultados.data.0.observacion_eval_apl'));
        $this->get(route('admin.institucional.ficha.postulante', $student))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('evaluacionesAplicadas', fn ($rows) => collect($rows)
                    ->pluck('codigo_eval_apl')->all() === ['RESULTADO-B-COMPARTIDA']));
    }

    public function test_multirole_global_scope_depends_on_administrator_permission_for_each_action(): void
    {
        $scope = app(AmbitoDocenteService::class);
        $this->teacherA->syncRoles(['Administrador', 'Docente']);
        $actor = $this->teacherA->fresh();

        $this->assertSame(Postulante::count(), $scope->postulantes(Postulante::query(), $actor)->count());
        $this->assertSame(GrupoAcademico::count(), $scope->grupos(GrupoAcademico::query(), $actor)->count());
        $this->assertSame(EvaluacionAplicada::count(), $scope->evaluaciones(EvaluacionAplicada::query(), $actor)->count());

        $admin = Role::findByName('Administrador');
        $admin->revokePermissionTo(['postulantes.ver', 'grupos.ver', 'resultados.ver']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $actor = $actor->fresh();

        $this->assertTrue($actor->can('postulantes.ver'));
        $this->assertTrue($actor->can('grupos.ver'));
        $this->assertTrue($actor->can('resultados.ver'));
        $this->assertSame(1, $scope->postulantes(Postulante::query(), $actor)->count());
        $this->assertSame(1, $scope->grupos(GrupoAcademico::query(), $actor)->count());
        $this->assertSame(1, $scope->evaluaciones(EvaluacionAplicada::query(), $actor)->count());

        $actor->syncRoles(['Docente', 'Administrador']);
        $actor = $actor->fresh();
        $this->assertSame(1, $scope->postulantes(Postulante::query(), $actor)->count());
        $this->assertSame(1, $scope->grupos(GrupoAcademico::query(), $actor)->count());
        $this->assertSame(1, $scope->evaluaciones(EvaluacionAplicada::query(), $actor)->count());
    }

    public function test_archived_group_cannot_accredit_derived_students_or_results(): void
    {
        GrupoAcademico::findOrFail($this->assignmentA->id_grupo)->delete();
        $scope = app(AmbitoDocenteService::class);

        $this->assertSame(0, $scope->grupos(GrupoAcademico::query(), $this->teacherA)->count());
        $this->assertSame(0, $scope->programas(ProgramaAcademico::query(), $this->teacherA, 'grupos.ver')->count());
        $this->assertSame(0, $scope->postulantes(Postulante::query(), $this->teacherA)->count());
        $this->assertSame(0, $scope->evaluaciones(EvaluacionAplicada::query(), $this->teacherA)->count());

        $this->actingAs($this->teacherA)
            ->get(route('admin.evaluaciones.resultados'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('resultados.total', 0)
                ->where('metricas.total', 0)
                ->where('plantillas', []));
        $this->get(route('postulantes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('postulantes.total', 0));
    }

    private function activeStudent(int $groupId): Postulante
    {
        return InscripcionAcademica::query()
            ->where('id_grupo', $groupId)
            ->where('estado_inscripcion', 'activo')
            ->with('postulante')
            ->firstOrFail()
            ->postulante;
    }

    /** @return array{User, TutorAcademico} */
    private function teacherWithTutor(string $name): array
    {
        $user = User::factory()->active()->create(['name' => $name])->assignRole('Docente');
        $personal = PersonalInstitucional::factory()->pending()->create([
            'user_id' => $user->id,
            'nombres' => $name,
            'apellidos' => 'Prueba',
        ]);
        $tutor = TutorAcademico::factory()->create(['personal_id' => $personal->id_personal]);

        return [$user, $tutor];
    }

    private function studentInGroup(GrupoAcademico $group, string $name, string $surname): Postulante
    {
        $student = Postulante::factory()->create([
            'nombres_post' => $name,
            'apellidos_post' => $surname,
            'email_post' => mb_strtolower($name).'@scope.test',
        ]);
        InscripcionAcademica::create([
            'id_prog' => $group->id_prog,
            'id_grupo' => $group->id_grupo,
            'id_post' => $student->id_post,
            'fecha_inscripcion' => today(),
            'estado_inscripcion' => 'activo',
        ]);

        return $student;
    }
}
