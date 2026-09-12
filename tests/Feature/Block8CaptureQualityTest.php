<?php

namespace Tests\Feature;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Support\BirthDate;
use App\Models\User;
use App\Support\Validation\AcademicDatePolicy;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class Block8CaptureQualityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-09-12 10:00:00', AcademicDatePolicy::TIMEZONE));
        $this->seed(RolesAndUsersSeeder::class);
        $this->admin = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $this->superAdmin = User::role('Super Administrador')->firstOrFail();
    }

    public function test_personal_and_cargo_capture_is_strict_for_new_records(): void
    {
        $cargo = Cargo::create([
            'nombre_cargo' => 'Docente',
            'descripcion' => 'Personal responsable de la enseñanza académica.',
            'estado' => 'activo',
        ]);
        $valid = [
            'nombres' => 'María Elena',
            'apellidos' => 'Quispe Rojas',
            'ci' => '8765432',
            'celular' => '70123456',
            'correo_contacto' => 'maria@example.test',
            'cargo_id' => $cargo->id_cargo,
        ];

        foreach ([
            ['nombres', '123'],
            ['apellidos', '@@@'],
            ['ci', 'abc'],
            ['celular', 'hola'],
            ['correo_contacto', 'abc'],
            ['cargo_id', null],
        ] as [$field, $value]) {
            $response = $this->actingAs($this->admin)->postJson(
                route('admin.institucional.personal.store'),
                array_replace($valid, [$field => $value]),
            );
            $this->assertSame(422, $response->status(), "El campo {$field} debía rechazarse.");
            $response->assertJsonValidationErrors($field);
        }

        $this->actingAs($this->admin)->postJson(route('admin.institucional.personal.store'), $valid)->assertRedirect();
        $this->assertDatabaseHas('personal_institucional', ['ci' => '8765432', 'estado' => 'pendiente']);
        $this->actingAs($this->admin)->postJson(route('admin.institucional.personal.store'), [...$valid, 'nombres' => 'Otra Persona'])->assertUnprocessable()->assertJsonValidationErrors('ci');

        $inactive = Cargo::create(['nombre_cargo' => 'Cargo inactivo', 'descripcion' => 'Descripción institucional válida.', 'estado' => 'inactivo']);
        $inactive->forceFill(['estado' => 'inactivo'])->save();
        $this->actingAs($this->admin)->postJson(route('admin.institucional.personal.store'), [...$valid, 'ci' => '8765433', 'cargo_id' => $inactive->id_cargo])->assertUnprocessable()->assertJsonValidationErrors('cargo_id');

        $this->actingAs($this->admin)->postJson(route('admin.institucional.cargos.store'), ['nombre_cargo' => 'Contador', 'descripcion' => 'corta'])->assertUnprocessable()->assertJsonValidationErrors('descripcion');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.cargos.store'), ['nombre_cargo' => '  DOCENTE  ', 'descripcion' => 'Otra descripción suficientemente extensa.'])->assertUnprocessable()->assertJsonValidationErrors('nombre_cargo');
    }

    public function test_planning_dates_groups_and_tutors_follow_new_capture_policy(): void
    {
        $program = [
            'nombre_prog' => 'Programa futuro',
            'codigo_prog' => 'FUT-01',
            'fecha_inicio_prog' => AcademicDatePolicy::tomorrowString(),
            'fecha_fin_prog' => AcademicDatePolicy::tomorrow()->addDay()->toDateString(),
            'estado_prog' => 'activo',
        ];
        $this->actingAs($this->admin)->postJson(route('admin.institucional.programas.store'), [...$program, 'fecha_inicio_prog' => AcademicDatePolicy::todayString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio_prog');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.programas.store'), [...$program, 'fecha_fin_prog' => $program['fecha_inicio_prog']])->assertUnprocessable()->assertJsonValidationErrors('fecha_fin_prog');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.programas.store'), $program)->assertRedirect();
        $programa = ProgramaAcademico::where('codigo_prog', 'FUT-01')->firstOrFail();

        $cargo = Cargo::create(['nombre_cargo' => 'Orientador', 'descripcion' => 'Orientación académica institucional.', 'estado' => 'activo']);
        $personal = PersonalInstitucional::create(['cargo_id' => $cargo->id_cargo, 'nombres' => 'Ana María', 'apellidos' => 'Flores Choque', 'ci' => '9000001', 'celular' => '70000001', 'correo_contacto' => 'ana@example.test']);
        $this->actingAs($this->admin)->get(route('admin.institucional.tutores.index'))
            ->assertInertia(fn (Assert $page) => $page->where('personas.0.id_personal', $personal->id_personal));

        $tutorData = ['personal_id' => $personal->id_personal, 'especialidad_tutor' => 'Matemática', 'formacion_tutor' => 'Licenciatura en Matemática', 'experiencia_tutor' => 'Experiencia en formación preuniversitaria.', 'estado_tutor' => 'activo'];
        foreach (['especialidad_tutor', 'formacion_tutor', 'experiencia_tutor'] as $field) {
            $this->actingAs($this->admin)->postJson(route('admin.institucional.tutores.store'), [...$tutorData, $field => null])->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->actingAs($this->admin)->postJson(route('admin.institucional.tutores.store'), $tutorData)->assertRedirect();
        $tutor = TutorAcademico::where('personal_id', $personal->id_personal)->firstOrFail();
        $this->assertNull($personal->fresh()->user_id);
        $this->assertDatabaseMissing('asignaciones_tutores', ['id_tutor' => $tutor->id_tutor]);
        $this->actingAs($this->admin)->postJson(route('admin.institucional.tutores.store'), $tutorData)->assertUnprocessable()->assertJsonValidationErrors('personal_id');

        $group = ['id_prog' => $programa->id_prog, 'nombre_grupo' => 'Grupo Mañana', 'codigo_grupo' => 'GRP-01', 'turno_grupo' => 'Mañana', 'aula_grupo' => '214', 'capacidad_grupo' => 20, 'nivel_grupo' => 'Preuniversitario', 'id_tutor_responsable' => $tutor->id_tutor, 'estado_grupo' => 'activo'];
        foreach ([['turno_grupo', 'inventado'], ['aula_grupo', 'A-214'], ['capacidad_grupo', 0], ['capacidad_grupo', 21], ['nivel_grupo', 'inventado']] as [$field, $value]) {
            $this->actingAs($this->admin)->postJson(route('admin.institucional.grupos.store'), [...$group, $field => $value])->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->actingAs($this->admin)->postJson(route('admin.institucional.grupos.store'), $group)->assertRedirect();
        $grupo = GrupoAcademico::where('codigo_grupo', 'GRP-01')->firstOrFail();

        $assignment = ['id_tutor' => $tutor->id_tutor, 'id_prog' => $programa->id_prog, 'id_grupo' => $grupo->id_grupo, 'fecha_inicio_asig' => AcademicDatePolicy::tomorrowString(), 'fecha_fin_asig' => AcademicDatePolicy::tomorrow()->addDay()->toDateString(), 'estado_asig' => 'activo'];
        $this->actingAs($this->admin)->postJson(route('admin.institucional.asignacion-tutores.store'), [...$assignment, 'fecha_inicio_asig' => AcademicDatePolicy::todayString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio_asig');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.asignacion-tutores.store'), $assignment)->assertRedirect();

        $simulation = ['id_prog' => $programa->id_prog, 'id_grupo' => $grupo->id_grupo, 'titulo_sim' => 'Simulacro futuro', 'fecha_sim' => AcademicDatePolicy::tomorrowString(), 'hora_inicio_sim' => '09:00', 'hora_fin_sim' => '10:00', 'estado_sim' => 'programado'];
        $this->actingAs($this->admin)->postJson(route('admin.institucional.simulacros.store'), [...$simulation, 'fecha_sim' => AcademicDatePolicy::todayString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_sim');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.simulacros.store'), [...$simulation, 'hora_fin_sim' => '09:00'])->assertUnprocessable()->assertJsonValidationErrors('hora_fin_sim');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.simulacros.store'), $simulation)->assertRedirect();
    }

    public function test_legacy_group_values_can_remain_unchanged_but_new_values_stay_strict(): void
    {
        $programa = ProgramaAcademico::create([
            'nombre_prog' => 'Programa legacy',
            'codigo_prog' => 'LEG-01',
            'estado_prog' => 'activo',
        ]);
        $aulaLegacy = GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => 'Grupo Aula',
            'codigo_grupo' => 'LEG-A',
            'turno_grupo' => 'Mañana',
            'aula_grupo' => 'Aula 101',
            'capacidad_grupo' => 30,
            'nivel_grupo' => 'Preuniversitario',
            'estado_grupo' => 'activo',
        ]);
        $laboratorioLegacy = GrupoAcademico::create([
            'id_prog' => $programa->id_prog,
            'nombre_grupo' => 'Grupo Laboratorio',
            'codigo_grupo' => 'LEG-L',
            'turno_grupo' => 'Tarde',
            'aula_grupo' => 'Laboratorio 1',
            'capacidad_grupo' => 35,
            'nivel_grupo' => 'Preuniversitario',
            'estado_grupo' => 'activo',
        ]);
        $payload = static fn (GrupoAcademico $grupo): array => [
            'id_prog' => $grupo->id_prog,
            'nombre_grupo' => $grupo->nombre_grupo,
            'codigo_grupo' => $grupo->codigo_grupo,
            'turno_grupo' => $grupo->turno_grupo,
            'aula_grupo' => $grupo->aula_grupo,
            'capacidad_grupo' => $grupo->capacidad_grupo,
            'nivel_grupo' => $grupo->nivel_grupo,
            'estado_grupo' => $grupo->estado_grupo,
        ];

        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $aulaLegacy),
            [...$payload($aulaLegacy), 'nombre_grupo' => 'Grupo Aula Actualizado'],
        )->assertRedirect();
        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $laboratorioLegacy),
            [...$payload($laboratorioLegacy), 'nombre_grupo' => 'Grupo Laboratorio Actualizado'],
        )->assertRedirect();
        $this->assertDatabaseHas('grupos_academicos', [
            'id_grupo' => $aulaLegacy->id_grupo,
            'aula_grupo' => 'Aula 101',
            'capacidad_grupo' => 30,
        ]);
        $this->assertDatabaseHas('grupos_academicos', [
            'id_grupo' => $laboratorioLegacy->id_grupo,
            'aula_grupo' => 'Laboratorio 1',
            'capacidad_grupo' => 35,
        ]);

        $aulaLegacy->refresh();
        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $aulaLegacy),
            [...$payload($aulaLegacy), 'aula_grupo' => 'A-214'],
        )->assertUnprocessable()->assertJsonValidationErrors('aula_grupo');
        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $aulaLegacy),
            [...$payload($aulaLegacy), 'aula_grupo' => '214'],
        )->assertRedirect();

        $aulaLegacy->refresh();
        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $aulaLegacy),
            [...$payload($aulaLegacy), 'capacidad_grupo' => 21],
        )->assertUnprocessable()->assertJsonValidationErrors('capacidad_grupo');
        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.grupos.update', $aulaLegacy),
            [...$payload($aulaLegacy), 'capacidad_grupo' => 20],
        )->assertRedirect();
    }

    public function test_historical_simulacro_with_null_hours_only_reprograms_on_a_real_temporal_change(): void
    {
        $programa = ProgramaAcademico::create([
            'nombre_prog' => 'Programa histórico',
            'codigo_prog' => 'HIS-01',
            'estado_prog' => 'activo',
        ]);
        $simulacro = SimulacroProgramado::create([
            'id_prog' => $programa->id_prog,
            'titulo_sim' => 'Simulacro histórico',
            'fecha_sim' => '2026-08-01',
            'hora_inicio_sim' => null,
            'hora_fin_sim' => null,
            'modalidad_sim' => 'Presencial',
            'estado_sim' => 'cerrado',
            'observacion_sim' => 'Registro histórico.',
        ]);
        $payload = [
            'id_prog' => $programa->id_prog,
            'id_grupo' => null,
            'id_plantilla' => null,
            'titulo_sim' => 'Simulacro histórico actualizado',
            'fecha_sim' => '2026-08-01',
            'hora_inicio_sim' => '',
            'hora_fin_sim' => '',
            'modalidad_sim' => 'Presencial',
            'estado_sim' => 'cerrado',
            'observacion_sim' => 'Observación actualizada.',
        ];

        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.simulacros.update', $simulacro),
            $payload,
        )->assertRedirect();
        $simulacro->refresh();
        $this->assertSame('Simulacro histórico actualizado', $simulacro->titulo_sim);
        $this->assertNull($simulacro->hora_inicio_sim);
        $this->assertNull($simulacro->hora_fin_sim);

        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.simulacros.update', $simulacro),
            [...$payload, 'hora_inicio_sim' => '09:00', 'hora_fin_sim' => '10:00'],
        )->assertUnprocessable()->assertJsonValidationErrors('fecha_sim');

        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.simulacros.update', $simulacro),
            [
                ...$payload,
                'fecha_sim' => AcademicDatePolicy::tomorrowString(),
                'hora_inicio_sim' => '09:00',
                'hora_fin_sim' => '09:00',
            ],
        )->assertUnprocessable()->assertJsonValidationErrors('hora_fin_sim');
        $this->actingAs($this->admin)->putJson(
            route('admin.institucional.simulacros.update', $simulacro),
            [
                ...$payload,
                'fecha_sim' => AcademicDatePolicy::tomorrowString(),
                'hora_inicio_sim' => '09:00',
                'hora_fin_sim' => '10:00',
            ],
        )->assertRedirect();
    }

    public function test_administrative_fact_dates_and_matricula_code_are_server_owned(): void
    {
        $programa = ProgramaAcademico::create(['nombre_prog' => 'Programa operativo', 'codigo_prog' => 'OPE-01', 'estado_prog' => 'activo']);
        $grupo = GrupoAcademico::create(['id_prog' => $programa->id_prog, 'nombre_grupo' => 'Grupo operativo', 'codigo_grupo' => 'OPE-G1', 'capacidad_grupo' => 20, 'estado_grupo' => 'activo']);
        $postulante = Postulante::create(['nombres_post' => 'Lucía', 'apellidos_post' => 'Mamani', 'fecha_nacimiento_post' => BirthDate::today()->subYears(18), 'gestion_post' => 2026, 'estado_post' => 'activo']);
        $enrollment = ['id_prog' => $programa->id_prog, 'id_grupo' => $grupo->id_grupo, 'id_post' => $postulante->id_post];

        $this->actingAs($this->admin)->postJson(route('admin.institucional.inscripciones.store'), [...$enrollment, 'fecha_inscripcion' => '2020-01-01'])->assertUnprocessable()->assertJsonValidationErrors('fecha_inscripcion');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.inscripciones.store'), $enrollment)->assertRedirect();
        $inscripcion = InscripcionAcademica::firstOrFail();
        $this->assertSame(AcademicDatePolicy::todayString(), $inscripcion->fecha_inscripcion->format('Y-m-d'));
        $this->assertSame('activo', $inscripcion->estado_inscripcion);

        $matriculaData = ['id_insc' => $inscripcion->id_insc, 'monto_matricula_mat' => 100, 'estado_matricula_mat' => 'activa'];
        $this->actingAs($this->admin)->postJson(route('admin.institucional.matriculas-cuotas.store'), [...$matriculaData, 'codigo_mat' => 'FALSO', 'fecha_matricula_mat' => '2020-01-01'])->assertUnprocessable()->assertJsonValidationErrors(['codigo_mat', 'fecha_matricula_mat']);
        $this->actingAs($this->admin)->postJson(route('admin.institucional.matriculas-cuotas.store'), $matriculaData)->assertRedirect();
        $matricula = MatriculaAcademica::firstOrFail();
        $this->assertMatchesRegularExpression('/^MTR-2026-\d{6}$/', $matricula->codigo_mat);
        $this->assertSame(AcademicDatePolicy::todayString(), $matricula->fecha_matricula_mat->format('Y-m-d'));

        $attendance = ['id_grupo' => $grupo->id_grupo, 'id_post' => $postulante->id_post, 'sesion_asist' => 'Primera', 'estado_asist' => 'presente'];
        $this->actingAs($this->admin)->postJson(route('admin.institucional.asistencia.store'), [...$attendance, 'fecha_asist' => '2020-01-01'])->assertUnprocessable()->assertJsonValidationErrors('fecha_asist');
        $this->actingAs($this->admin)->postJson(route('admin.institucional.asistencia.store'), $attendance)->assertRedirect();
        $this->assertSame(AcademicDatePolicy::todayString(), $postulante->asistenciasAcademicas()->where('sesion_asist', 'Primera')->firstOrFail()->fecha_asist->format('Y-m-d'));

        $this->actingAs($this->admin)->postJson(route('admin.institucional.asistencia.store-grupo'), ['id_grupo' => $grupo->id_grupo, 'sesion_asist' => 'Segunda', 'registros' => [['id_post' => $postulante->id_post, 'estado_asist' => 'presente']]])->assertRedirect();
        $this->assertSame(AcademicDatePolicy::todayString(), $postulante->asistenciasAcademicas()->where('sesion_asist', 'Segunda')->firstOrFail()->fecha_asist->format('Y-m-d'));
    }

    public function test_only_active_verified_super_administrator_can_mutate_materias(): void
    {
        $teacher = User::role('Docente')->firstOrFail();
        $payload = ['codigo_mat' => 'BIO', 'nombre_mat' => 'Biología', 'descripcion_mat' => 'Fundamentos científicos de biología general.'];

        $this->actingAs($this->admin)->get(route('admin.evaluaciones.materias'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.evaluaciones.materias'))->assertOk();
        $this->actingAs($this->admin)->postJson(route('admin.evaluaciones.materias.store'), $payload)->assertForbidden();
        $this->actingAs($teacher)->postJson(route('admin.evaluaciones.materias.store'), $payload)->assertForbidden();
        $this->admin->givePermissionTo('materias.crear');
        $this->admin->assignRole('Docente');
        $this->actingAs($this->admin->fresh())->postJson(route('admin.evaluaciones.materias.store'), $payload)->assertForbidden();
        $this->actingAs($this->superAdmin)->postJson(route('admin.evaluaciones.materias.store'), $payload)->assertRedirect();
        $materia = Materia::where('codigo_mat', 'BIO')->firstOrFail();
        $this->actingAs($this->superAdmin)->postJson(route('admin.evaluaciones.materias.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('codigo_mat');
        $this->actingAs($this->superAdmin)->patchJson(route('admin.evaluaciones.materias.estado', $materia), ['estado_mat' => 'inactivo'])->assertRedirect();
        $this->assertSame('inactivo', $materia->fresh()->estado_mat);
        $this->assertFalse(app('router')->getRoutes()->getByName('admin.evaluaciones.materias.destroy') !== null);
    }
}
