<?php

namespace Tests\Feature\Academico;

use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use App\Support\Validation\AcademicDatePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MatriculasCuotasPantallaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private InscripcionAcademica $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();

        // Test fixtures only: no institutional seeder or DEMO account recovery.
        $rol = Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
        foreach (['ver', 'crear', 'editar'] as $accion) {
            $rol->givePermissionTo(Permission::create(['name' => 'matriculas-cuotas.'.$accion, 'guard_name' => 'web']));
        }
        $this->admin = User::factory()->create();
        $this->admin->assignRole($rol);
        $programa = ProgramaAcademico::create(['nombre_prog' => 'Programa QA', 'codigo_prog' => 'QA', 'estado_prog' => 'activo']);
        $grupo = GrupoAcademico::create(['id_prog' => $programa->id_prog, 'nombre_grupo' => 'Grupo QA', 'codigo_grupo' => 'QA-A', 'capacidad_grupo' => 20, 'estado_grupo' => 'activo']);
        $postulante = Postulante::factory()->create(['nombres_post' => 'Estudiante', 'apellidos_post' => 'Prueba']);
        $this->inscripcion = InscripcionAcademica::create(['id_post' => $postulante->id_post, 'id_prog' => $programa->id_prog, 'id_grupo' => $grupo->id_grupo, 'fecha_inscripcion' => AcademicDatePolicy::todayString(), 'estado_inscripcion' => 'activo']);
    }

    public function test_index_loads_inertia_with_options_totals_and_bolivia_date(): void
    {
        $this->actingAs($this->admin)->get(route('admin.institucional.matriculas-cuotas.index'))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Institucional/MatriculasCuotas/Index')
            ->where('fechaHoy', AcademicDatePolicy::todayString())
            ->where('matriculas.total', 0)->has('programas', 1)->has('grupos', 1)->has('inscripciones', 1));

        $matricula = $this->crearMatricula();
        CuotaAcademica::create(['id_mat' => $matricula->id_mat, 'nro_cuota' => 1, 'monto_cuota' => 123.45, 'estado_cuota' => 'vencida']);
        CuotaAcademica::create(['id_mat' => $matricula->id_mat, 'nro_cuota' => 2, 'monto_cuota' => 50, 'estado_cuota' => 'pagada', 'fecha_pago_cuota' => AcademicDatePolicy::todayString()]);

        $this->get(route('admin.institucional.matriculas-cuotas.index', [
            'buscar' => 'estudiante', 'id_prog' => $this->inscripcion->id_prog, 'id_grupo' => $this->inscripcion->id_grupo,
            'estado_matricula_mat' => 'activa', 'estado_cuota' => 'vencida',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('matriculas.total', 1)->where('matriculas.data.0.id_mat', $matricula->id_mat)
            ->where('matriculas.data.0.cuotas_count', 2)->where('matriculas.data.0.cuotas_vencidas_count', 1)
            ->where('matriculas.data.0.saldo_pendiente', fn ($saldo) => (float) $saldo === 123.45)
            ->has('matriculas.data.0.cuotas', 2)->where('metricas.cuotasVencidas', 1));

        $this->get(route('admin.institucional.matriculas-cuotas.index', ['buscar' => 'sin coincidencias']))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('matriculas.total', 0));
    }

    public function test_create_and_edit_matricula_preserves_derived_identity_and_audits(): void
    {
        $matricula = $this->crearMatricula();
        $identidad = $matricula->only(['id_post', 'id_insc', 'id_prog', 'id_grupo', 'codigo_mat', 'fecha_matricula_mat']);
        $this->patch(route('admin.institucional.matriculas-cuotas.update', $matricula), [
            ...$this->matriculaPayload(), 'monto_matricula_mat' => 321.25, 'observacion_mat' => 'Verificación aislada',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals($identidad, $matricula->refresh()->only(array_keys($identidad)));
        $this->assertSame('321.25', $matricula->monto_matricula_mat);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'crear_matricula', 'entidad_id' => (string) $matricula->id_mat]);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'editar_matricula', 'entidad_id' => (string) $matricula->id_mat]);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('postulantes', 1);
        $this->assertDatabaseCount('inscripciones_academicas', 1);
    }

    public function test_cuota_create_edit_validation_and_automatic_debt_rules(): void
    {
        $matricula = $this->crearMatricula();
        $payload = ['id_mat' => $matricula->id_mat, 'nro_cuota' => 1, 'monto_cuota' => 150.50, 'estado_cuota' => 'pendiente'];
        $this->post(route('admin.institucional.cuotas.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $cuota = CuotaAcademica::sole();
        $this->assertTrue($matricula->habilitacion()->firstOrFail()->habilitado_evaluaciones_hab);

        $this->postJson(route('admin.institucional.cuotas.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('nro_cuota');
        $this->patchJson(route('admin.institucional.cuotas.update', $cuota), [...$payload, 'estado_cuota' => 'pagada'])
            ->assertUnprocessable()->assertJsonValidationErrors('fecha_pago_cuota');
        $this->assertSame('pendiente', $cuota->refresh()->estado_cuota);

        $this->patch(route('admin.institucional.cuotas.update', $cuota), [...$payload, 'estado_cuota' => 'vencida'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse($matricula->habilitacion()->firstOrFail()->habilitado_evaluaciones_hab);
        $this->patch(route('admin.institucional.cuotas.update', $cuota), [...$payload, 'estado_cuota' => 'pagada', 'fecha_pago_cuota' => AcademicDatePolicy::todayString()])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($matricula->habilitacion()->firstOrFail()->habilitado_evaluaciones_hab);
        $this->assertSame('150.50', $cuota->refresh()->monto_cuota);
        $this->assertDatabaseCount('cuotas_academicas', 1);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'crear_cuota', 'entidad_id' => (string) $cuota->id_cuota]);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'editar_cuota', 'entidad_id' => (string) $cuota->id_cuota]);
    }

    public function test_queries_paginate_and_reject_invalid_filters(): void
    {
        $matricula = $this->crearMatricula();
        foreach (range(1, 10) as $index) {
            $postulante = Postulante::factory()->create();
            $inscripcion = $this->inscripcion->replicate();
            $inscripcion->id_post = $postulante->id_post;
            $inscripcion->save();
            $otra = $matricula->replicate();
            $otra->id_insc = $inscripcion->id_insc;
            $otra->id_post = $postulante->id_post;
            $otra->codigo_mat = 'QA-'.$index;
            $otra->save();
        }
        $this->get(route('admin.institucional.matriculas-cuotas.index', ['page' => 2]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('matriculas.total', 11)->where('matriculas.current_page', 2)->has('matriculas.data', 1));
        $this->getJson(route('admin.institucional.matriculas-cuotas.index', ['estado_cuota' => 'incorrecto']))
            ->assertUnprocessable()->assertJsonValidationErrors('estado_cuota');
    }

    public function test_user_without_permission_cannot_read_or_write_financial_records(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.institucional.matriculas-cuotas.index'))->assertForbidden();
        $this->postJson(route('admin.institucional.matriculas-cuotas.store'), $this->matriculaPayload())->assertForbidden();
        $this->postJson(route('admin.institucional.cuotas.store'), [])->assertForbidden();
        $this->assertDatabaseCount('matriculas_academicas', 0);
        $this->assertDatabaseCount('cuotas_academicas', 0);
    }

    private function matriculaPayload(): array
    {
        return ['id_insc' => $this->inscripcion->id_insc, 'monto_matricula_mat' => 200, 'estado_matricula_mat' => 'activa'];
    }

    private function crearMatricula(): MatriculaAcademica
    {
        $this->actingAs($this->admin)->post(route('admin.institucional.matriculas-cuotas.store'), $this->matriculaPayload())
            ->assertRedirect()->assertSessionHasNoErrors();

        return MatriculaAcademica::sole();
    }
}
