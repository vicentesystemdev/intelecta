<?php

namespace Tests\Feature\Institucional;

use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Repositories\TutorAcademicoRepository;
use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Support\PermisosOrganizacion;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Database\Seeders\TutoresAcademicosSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TutorPersonalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (['Administrador', 'Super Administrador', 'Docente', 'Estudiante'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        foreach (['tutores.ver', 'tutores.crear', 'tutores.editar'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        Role::findByName('Administrador')->givePermissionTo([...PermisosOrganizacion::ALL, 'tutores.ver', 'tutores.crear', 'tutores.editar']);
        $this->admin = User::factory()->active()->create()->assignRole('Administrador');
        $this->actingAs($this->admin);
    }

    private function payload(PersonalInstitucional $person, array $extra = []): array
    {
        return array_replace(['personal_id' => $person->id_personal, 'estado_tutor' => 'activo', 'especialidad_tutor' => 'Física'], $extra);
    }

    private function url(string $action, mixed $id = []): string
    {
        return route('admin.institucional.tutores.'.$action, $id);
    }

    public function test_creation_requires_explicit_personal_and_never_creates_account_or_cargo(): void
    {
        $this->postJson($this->url('store'), ['estado_tutor' => 'activo'])->assertUnprocessable()->assertJsonValidationErrors('personal_id');
        $person = PersonalInstitucional::factory()->pending()->create();
        $this->postJson($this->url('store'), $this->payload($person))->assertRedirect();
        $tutor = TutorAcademico::sole();
        $this->assertSame($person->id_personal, $tutor->personal_id);
        $this->assertSame('María Elena Quispe Rojas', $tutor->nombre_completo);
        $this->assertNull($tutor->personal->user);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('cargos', 0);
        $this->assertSame('pendiente', $person->fresh()->estado->value);
        Notification::assertNothingSent();
    }

    public function test_identity_and_profession_save_to_distinct_entities_without_touching_security(): void
    {
        $user = User::factory()->active()->create()->assignRole('Docente');
        $person = PersonalInstitucional::factory()->create(['user_id' => $user->id]);
        $tutor = TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        $security = $user->fresh()->getAttributes();
        $this->patchJson($this->url('update', $tutor), $this->payload($person, ['personal' => [
            'nombres' => '  María   Luz ', 'apellidos' => 'Quispe Rojas', 'ci' => '7654321',
            'celular' => '+591 72010001', 'correo_contacto' => 'CONTACTO@EXAMPLE.COM',
        ]]))->assertRedirect();
        $this->assertSame('María Luz', $person->fresh()->nombres);
        $this->assertSame('+59172010001', $person->fresh()->celular);
        $this->assertSame('contacto@example.com', $person->fresh()->correo_contacto);
        $this->assertSame('Física', $tutor->fresh()->especialidad_tutor);
        $this->assertSame($security, $user->fresh()->getAttributes());
        $this->assertSame(['Docente'], $user->fresh()->getRoleNames()->all());
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'editar_personal', 'entidad' => 'personal_institucional']);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'editar_tutor', 'entidad' => 'tutores_academicos']);
        $audit = DB::table('bitacora_sistema')->whereIn('accion', ['editar_personal', 'editar_tutor'])->get()->toJson();
        $this->assertStringNotContainsString('7654321', $audit);
        $before = $person->fresh()->getAttributes();
        $this->patchJson($this->url('update', $tutor), $this->payload($person, ['especialidad_tutor' => 'Química']))->assertRedirect();
        $this->assertSame($before, $person->fresh()->getAttributes());
    }

    public function test_archived_tutor_reserves_personal_and_keeps_user(): void
    {
        $person = PersonalInstitucional::factory()->create(['user_id' => $this->admin->id]);
        $tutor = TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        $tutor->delete();
        $this->postJson($this->url('store'), $this->payload($person))->assertUnprocessable()->assertJsonValidationErrors('personal_id');
        $this->assertTrue($person->fresh()->tutorAcademico->trashed());
        $this->assertSame($this->admin->id, $person->fresh()->user->id);
        $this->assertCount(0, app(TutorAcademicoRepository::class)->personalOptions());
    }

    public function test_inactive_personal_is_not_eligible_for_new_tutor_but_existing_tutor_is_editable(): void
    {
        $person = PersonalInstitucional::factory()->inactive()->create();
        $this->postJson($this->url('store'), $this->payload($person))->assertUnprocessable()->assertJsonValidationErrors('personal_id');
        $tutor = TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        $this->patchJson($this->url('update', $tutor), $this->payload($person))->assertRedirect();
        $this->assertSame(EstadoPersonal::INACTIVO, $person->fresh()->estado);
        $this->assertSame('activo', $tutor->fresh()->estado_tutor);
    }

    public function test_states_are_independent_in_all_three_directions(): void
    {
        $user = User::factory()->active()->create()->assignRole('Docente');
        $person = PersonalInstitucional::factory()->active()->create(['user_id' => $user->id]);
        $tutor = TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        $this->patchJson($this->url('update', $tutor), $this->payload($person, ['estado_tutor' => 'inactivo']))->assertRedirect();
        $this->assertSame('activo', $person->fresh()->estado->value);
        $this->assertSame('activa', $user->fresh()->estado_cuenta->value);
        $this->patchJson(route('admin.institucional.personal.estado', $person), ['estado' => 'inactivo'])->assertRedirect();
        $this->assertSame('inactivo', $tutor->fresh()->estado_tutor);
        $this->assertSame('activa', $user->fresh()->estado_cuenta->value);
        $sa = User::factory()->active()->create()->assignRole('Super Administrador');
        app(CuentaService::class)->block($sa, $user->id, 'Prueba de independencia de estados');
        $this->assertSame('inactivo', $person->fresh()->estado->value);
        $this->assertSame('inactivo', $tutor->fresh()->estado_tutor);
    }

    public function test_personal_identity_cannot_be_reassigned_on_tutor_update(): void
    {
        $tutor = TutorAcademico::factory()->withPersonal()->create();
        $other = PersonalInstitucional::factory()->create();
        $this->patchJson($this->url('update', $tutor), $this->payload($other, ['personal' => ['nombres' => 'Otro Nombre', 'apellidos' => 'Otros Apellidos']]))->assertUnprocessable();
        $this->assertSame($tutor->personal_id, $tutor->fresh()->personal_id);
        $this->assertSame('María Elena', $other->fresh()->nombres);
    }

    public static function forbiddenFields(): array
    {
        return array_map(fn ($field) => [$field], ['user_id', 'email', 'password', 'estado_cuenta', 'email_verified_at', 'role', 'roles', 'permissions', 'cargo_id', 'estado', 'nombres_tutor', 'correo_tutor']);
    }

    #[DataProvider('forbiddenFields')]
    public function test_tutor_rejects_security_and_legacy_fields(string $field): void
    {
        $person = PersonalInstitucional::factory()->create();
        $this->postJson($this->url('store'), $this->payload($person, [$field => 'manipulado']))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('tutores_academicos', 0);
    }

    public function test_personal_edit_permission_is_separate_and_nested_extra_keys_are_rejected(): void
    {
        $tutor = TutorAcademico::factory()->withPersonal()->create();
        $this->patchJson($this->url('update', $tutor), $this->payload($tutor->personal, ['personal' => ['nombres' => 'María', 'apellidos' => 'Quispe', 'user_id' => $this->admin->id]]))->assertUnprocessable()->assertJsonValidationErrors('personal');
        Role::findByName('Administrador')->revokePermissionTo('personal.editar');
        $this->admin->unsetRelation('roles');
        $this->patchJson($this->url('update', $tutor), $this->payload($tutor->personal))->assertRedirect();
        $this->patchJson($this->url('update', $tutor), $this->payload($tutor->personal, ['personal' => ['nombres' => 'María', 'apellidos' => 'Quispe']]))->assertForbidden();
    }

    public function test_teacher_profile_does_not_grant_global_crud(): void
    {
        $teacher = User::factory()->active()->create()->assignRole('Docente');
        $person = PersonalInstitucional::factory()->create(['user_id' => $teacher->id]);
        TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        $this->actingAs($teacher)->get($this->url('index'))->assertForbidden();
        $this->postJson($this->url('store'), $this->payload($person))->assertForbidden();
    }

    public function test_search_pagination_detail_and_eager_loading_use_personal(): void
    {
        $person = PersonalInstitucional::factory()->create(['nombres' => 'Ana Lucía', 'ci' => '8877665', 'correo_contacto' => 'ana@example.com', 'celular' => '72012345']);
        $tutor = TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
        foreach (['Lucía', '8877665', 'ana@example.com', '72012345'] as $search) {
            $page = app(TutorAcademicoRepository::class)->paginate(['buscar' => $search]);
            $this->assertSame(1, $page->total());
            $this->assertSame('Ana Lucía Quispe Rojas', $page->items()[0]->nombre_completo);
        }
        TutorAcademico::factory()->count(13)->withPersonal()->create();
        $this->assertCount(12, app(TutorAcademicoRepository::class)->paginate([])->items());
        $page = app(TutorAcademicoRepository::class)->paginate([]);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $page->toArray();
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
        $this->get($this->url('show', $tutor))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Institucional/Tutores/Show')->where('tutor.personal.nombres', 'Ana Lucía')->missing('tutor.user_id')->missing('tutor.nombres_tutor'));
    }

    public function test_factory_requires_explicit_personal_and_alternate_seeder_is_idempotent(): void
    {
        $personal = [];
        foreach (['matematica', 'fisica', 'quimica', 'razonamiento', 'paa'] as $key) {
            $personal[$key] = PersonalInstitucional::factory()->create();
        }
        $seed = app(TutoresAcademicosSeeder::class);
        $seed->run($personal);
        $seed->run($personal);
        $this->assertDatabaseCount('tutores_academicos', 5);
        $this->assertDatabaseCount('personal_institucional', 5);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('cargos', 0);
        $this->expectException(QueryException::class);
        TutorAcademico::factory()->create();
    }

    public function test_database_restricts_deleting_personal_and_reserves_archived_link(): void
    {
        $tutor = TutorAcademico::factory()->withPersonal()->create();
        $tutor->delete();
        $this->expectException(QueryException::class);
        $tutor->personal->delete();
    }

    public function test_invalid_personal_identifiers_and_duplicate_ci_fail_before_writes(): void
    {
        foreach ([null, 'abc', [], -1, '1.5', 9999999] as $id) {
            $this->postJson($this->url('store'), ['personal_id' => $id, 'estado_tutor' => 'activo'])->assertUnprocessable()->assertJsonValidationErrors('personal_id');
        }
        PersonalInstitucional::factory()->create(['ci' => '1122334']);
        $person = PersonalInstitucional::factory()->create();
        $this->postJson($this->url('store'), $this->payload($person, ['personal' => ['nombres' => 'Nuevo Nombre', 'apellidos' => 'Otros Apellidos', 'ci' => '1122334']]))->assertUnprocessable()->assertJsonValidationErrors('personal.ci');
        $this->assertSame('María Elena', $person->fresh()->nombres);
        $this->assertDatabaseCount('tutores_academicos', 0);
    }
}
