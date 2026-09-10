<?php

namespace Tests\Feature\Institucional;

use App\Domains\Institucional\Actions\VincularUsuarioPersonalAction;
use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Support\PermisosOrganizacion;
use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizacionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $sa;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (['Administrador', 'Super Administrador', 'Docente', 'Estudiante'] as $name) {
            Role::findOrCreate($name, 'web');
        }
        Role::findByName('Administrador')->givePermissionTo(PermisosOrganizacion::ALL);
        $this->admin = User::factory()->active()->create()->assignRole('Administrador');
        $this->sa = User::factory()->active()->create()->assignRole('Super Administrador');
        $this->actingAs($this->admin);
    }

    private function url(string $route, mixed $parameters = []): string
    {
        return route('admin.institucional.'.$route, $parameters);
    }

    private function person(array $extra = []): array
    {
        return array_replace(['nombres' => 'María José', 'apellidos' => "Muñoz O'Connor", 'ci' => null, 'celular' => null, 'correo_contacto' => null, 'cargo_id' => null], $extra);
    }

    public function test_admin_creates_edits_and_inactivates_cargo(): void
    {
        $this->postJson($this->url('cargos.store'), ['nombre_cargo' => '  Coordinador   Académico  ', 'descripcion' => 'Organización académica'])->assertRedirect();
        $cargo = Cargo::firstOrFail();
        $this->assertSame('Coordinador Académico', $cargo->nombre_cargo);
        $this->assertSame(EstadoCargo::ACTIVO, $cargo->estado);
        $this->putJson($this->url('cargos.update', $cargo), ['nombre_cargo' => 'Coordinación Académica', 'descripcion' => null])->assertRedirect();
        $this->patchJson($this->url('cargos.estado', $cargo), ['estado' => 'inactivo'])->assertRedirect();
        $this->assertSame(EstadoCargo::INACTIVO, $cargo->fresh()->estado);
        foreach (['crear_cargo', 'editar_cargo', 'cambiar_estado_cargo'] as $action) {
            $this->assertDatabaseHas('bitacora_sistema', ['accion' => $action, 'user_id' => $this->admin->id]);
        }
    }

    public static function invalidCargos(): array
    {
        return [[''], ['123456'], ['!!'], [str_repeat('a', 161)], [['Docente']], [true], ["Cargo\x01"]];
    }

    #[DataProvider('invalidCargos')]
    public function test_invalid_cargo_names_are_rejected(mixed $name): void
    {
        $this->postJson($this->url('cargos.store'), ['nombre_cargo' => $name])->assertUnprocessable()->assertJsonValidationErrors('nombre_cargo');
        $this->assertDatabaseCount('cargos', 0);
    }

    public function test_normalized_name_uniqueness_and_self_edit(): void
    {
        $cargo = Cargo::factory()->create(['nombre_cargo' => 'Director de Carrera']);
        $this->postJson($this->url('cargos.store'), ['nombre_cargo' => ' director   DE carrera '])->assertUnprocessable()->assertJsonValidationErrors('nombre_cargo');
        $this->putJson($this->url('cargos.update', $cargo), ['nombre_cargo' => 'DIRECTOR DE CARRERA'])->assertRedirect();
        $this->assertDatabaseCount('cargos', 1);
    }

    public function test_cargo_inactivation_preserves_assignments_and_account_security(): void
    {
        $cargo = Cargo::factory()->create();
        $record = PersonalInstitucional::factory()->active()->create(['user_id' => $this->admin->id, 'cargo_id' => $cargo->id_cargo]);
        $before = $this->admin->fresh()->getRawOriginal();
        $roles = $this->admin->getRoleNames()->all();
        $permissions = $this->admin->getAllPermissions()->pluck('id')->all();
        $this->patchJson($this->url('cargos.estado', $cargo), ['estado' => 'inactivo'])->assertRedirect();
        $this->assertSame($cargo->id_cargo, $record->fresh()->cargo_id);
        $this->postJson($this->url('personal.store'), $this->person(['cargo_id' => $cargo->id_cargo]))->assertUnprocessable()->assertJsonValidationErrors('cargo_id');
        $this->putJson($this->url('personal.update', $record), $this->person(['cargo_id' => $cargo->id_cargo]))->assertRedirect();
        $this->get($this->url('personal.index'))->assertInertia(fn (Assert $page) => $page->has('cargosActivos', 0)->where('personal.data.0.cargo.estado', 'inactivo'));
        $this->assertSame($before, $this->admin->fresh()->getRawOriginal());
        $this->assertSame($roles, $this->admin->fresh()->getRoleNames()->all());
        $this->assertSame($permissions, $this->admin->fresh()->getAllPermissions()->pluck('id')->all());
    }

    public function test_personal_can_exist_without_user_or_cargo_and_does_not_match_email(): void
    {
        $this->postJson($this->url('personal.store'), $this->person(['correo_contacto' => strtoupper($this->admin->email)]))->assertRedirect();
        $record = PersonalInstitucional::firstOrFail();
        $this->assertNull($record->user_id);
        $this->assertNull($record->cargo_id);
        $this->assertNull($this->admin->fresh()->personalInstitucional);
        $this->assertSame($this->admin->email, $record->correo_contacto);
        $this->assertSame(EstadoPersonal::PENDIENTE, $record->estado);
        $this->assertDatabaseCount('users', 2);
        Notification::assertNothingSent();
    }

    public static function invalidPersonal(): array
    {
        return [
            ['nombres', '123456'], ['nombres', 'María123'], ['nombres', ''], ['apellidos', '@Quispe'], ['apellidos', ['Quispe']],
            ['ci', 'ABCD'], ['ci', '123@45'], ['ci', '123'], ['celular', '777abc12'], ['celular', '123'],
            ['correo_contacto', 'persona@localhost'], ['correo_contacto', ['a@example.com']], ['cargo_id', true], ['cargo_id', 999999],
        ];
    }

    #[DataProvider('invalidPersonal')]
    public function test_personal_reuses_shared_validation(string $field, mixed $value): void
    {
        $this->postJson($this->url('personal.store'), $this->person([$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('personal_institucional', 0);
    }

    public function test_personal_normalization_and_unique_optional_ci(): void
    {
        $data = $this->person(['nombres' => '  José   Ángel ', 'ci' => ' 1234567-1A LP ', 'celular' => '+591 (777)-12345', 'correo_contacto' => ' CONTACTO@EXAMPLE.COM ']);
        $this->postJson($this->url('personal.store'), $data)->assertRedirect();
        $record = PersonalInstitucional::firstOrFail();
        $this->assertSame('José Ángel', $record->nombres);
        $this->assertSame('+59177712345', $record->celular);
        $this->assertSame('contacto@example.com', $record->correo_contacto);
        $this->postJson($this->url('personal.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('ci');
        $this->putJson($this->url('personal.update', $record), $data)->assertRedirect();
        $this->postJson($this->url('personal.store'), $this->person())->assertRedirect();
        $this->postJson($this->url('personal.store'), $this->person())->assertRedirect();
        $this->assertDatabaseCount('personal_institucional', 3);
    }

    public function test_crud_cannot_write_user_identity_security_or_bypass_state_permission(): void
    {
        $payload = $this->person(['user_id' => $this->sa->id, 'estado' => 'activo', 'email' => 'new@example.com', 'password' => 'Secret123', 'estado_cuenta' => 'activa', 'email_verified_at' => '2026-09-09', 'role' => 'Super Administrador', 'roles' => ['Super Administrador'], 'permissions' => ['usuarios.crear']]);
        $this->postJson($this->url('personal.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors(['user_id', 'estado', 'email', 'password', 'estado_cuenta', 'email_verified_at', 'role', 'roles', 'permissions']);
        $record = PersonalInstitucional::factory()->create();
        $this->putJson($this->url('personal.update', $record), $payload)->assertUnprocessable();
        $this->assertNull($record->fresh()->user_id);
        $this->postJson($this->url('cargos.store'), ['nombre_cargo' => 'Director', 'estado' => 'inactivo', 'role_id' => 1])->assertUnprocessable()->assertJsonValidationErrors(['estado', 'role_id']);
    }

    public function test_personal_updates_cargo_and_state_do_not_change_account_or_permissions(): void
    {
        $first = Cargo::factory()->create();
        $second = Cargo::factory()->create();
        $record = PersonalInstitucional::factory()->active()->create(['user_id' => $this->admin->id, 'cargo_id' => $first->id_cargo]);
        $before = $this->admin->fresh()->getRawOriginal();
        $roles = $this->admin->getRoleNames()->all();
        $permissions = $this->admin->getAllPermissions()->pluck('id')->all();
        $this->putJson($this->url('personal.update', $record), $this->person(['cargo_id' => $second->id_cargo, 'correo_contacto' => 'independiente@example.com']))->assertRedirect();
        foreach (['inactivo', 'pendiente', 'activo'] as $state) {
            $this->patchJson($this->url('personal.estado', $record), ['estado' => $state])->assertRedirect();
            $this->assertSame($state, $record->fresh()->estado->value);
            $this->assertSame($before, $this->admin->fresh()->getRawOriginal());
        }
        $this->assertSame($roles, $this->admin->fresh()->getRoleNames()->all());
        $this->assertSame($permissions, $this->admin->fresh()->getAllPermissions()->pluck('id')->all());
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'cambiar_cargo_personal', 'entidad_id' => (string) $record->id_personal]);
    }

    public function test_account_block_unlock_and_email_change_leave_personal_intact(): void
    {
        $record = PersonalInstitucional::factory()->active()->create(['user_id' => $this->admin->id, 'correo_contacto' => 'contacto@example.com']);
        $before = $record->fresh()->getRawOriginal();
        $service = app(CuentaService::class);
        $service->block($this->sa, $this->admin->id, 'Suspensión de prueba institucional');
        $this->assertSame($before, $record->fresh()->getRawOriginal());
        $service->unblock($this->sa, $this->admin->id);
        $service->update($this->sa, $this->admin->id, ['name' => $this->admin->name, 'email' => 'acceso-nuevo@example.com']);
        $this->assertSame($before, $record->fresh()->getRawOriginal());
        $this->assertSame(EstadoCuenta::PENDIENTE, $this->admin->fresh()->estado_cuenta);
    }

    public function test_sa_links_by_explicit_ids_without_requiring_role_or_active_target(): void
    {
        $target = User::factory()->blocked()->create();
        $record = PersonalInstitucional::factory()->create(['correo_contacto' => $this->admin->email]);
        $this->actingAs($this->sa)->postJson($this->url('personal.vincular', $record), ['user_id' => $target->id, 'motivo' => 'Identidad confirmada por documentación externa'])->assertRedirect();
        $this->assertSame($target->id, $record->fresh()->user_id);
        $this->assertSame($record->id_personal, $target->fresh()->personalInstitucional->id_personal);
        $this->assertSame(EstadoCuenta::BLOQUEADA, $target->fresh()->estado_cuenta);
        $this->assertSame([], $target->fresh()->getRoleNames()->all());
        $this->assertNull($target->fresh()->postulante);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'vincular_usuario_personal', 'entidad_id' => (string) $record->id_personal]);
    }

    public function test_identity_cannot_be_replaced_reused_or_detached(): void
    {
        $record = PersonalInstitucional::factory()->create(['user_id' => $this->admin->id]);
        $other = PersonalInstitucional::factory()->create();
        $this->actingAs($this->sa);
        foreach ([[$record, $this->sa], [$other, $this->admin], [$record, $this->admin]] as [$person, $user]) {
            $this->postJson($this->url('personal.vincular', $person), ['user_id' => $user->id, 'motivo' => 'Intento de reasignación de prueba'])->assertUnprocessable()->assertJsonValidationErrors('user_id');
        }
        $this->putJson($this->url('personal.update', $record), $this->person(['user_id' => null]))->assertRedirect();
        $this->assertSame($this->admin->id, $record->fresh()->user_id);
        $this->assertNull($other->fresh()->user_id);
    }

    public function test_identity_action_validates_ids_and_actor(): void
    {
        $record = PersonalInstitucional::factory()->create();
        $this->actingAs($this->sa)->postJson($this->url('personal.vincular', $record), ['user_id' => 999999, 'motivo' => 'Confirmación de prueba'])->assertUnprocessable();
        $this->postJson($this->url('personal.vincular', $record), ['user_id' => true, 'motivo' => 'Confirmación de prueba'])->assertUnprocessable();
        $this->expectException(ValidationException::class);
        app(VincularUsuarioPersonalAction::class)->execute(999999, $record->id_personal, $this->sa, 'Confirmación de prueba');
    }

    public function test_eligible_search_is_ti_only_excludes_linked_accounts_and_accepts_single_digit_id(): void
    {
        PersonalInstitucional::factory()->create(['user_id' => $this->admin->id]);
        $this->getJson($this->url('personal.usuarios-elegibles', ['buscar' => 'example']))->assertForbidden();
        $this->actingAs($this->sa)->getJson($this->url('personal.usuarios-elegibles', ['buscar' => (string) $this->sa->id]))->assertOk()->assertJsonFragment(['id' => $this->sa->id]);
        $this->getJson($this->url('personal.usuarios-elegibles', ['buscar' => $this->admin->email]))->assertOk()->assertJsonPath('total', 0);
        $this->getJson($this->url('personal.usuarios-elegibles', ['buscar' => str_repeat('9', 100)]))->assertOk()->assertJsonPath('total', 0);
    }

    public function test_admin_can_manage_organization_but_not_link_identity_or_control_account_security(): void
    {
        $record = PersonalInstitucional::factory()->create();
        $this->get($this->url('personal.index'))->assertInertia(fn (Assert $page) => $page->where('permisos.crear', true)->where('permisos.vincular', false)->where('auth.organization.personal', true));
        $this->postJson($this->url('personal.vincular', $record), ['user_id' => $this->sa->id, 'motivo' => 'Intento no autorizado'])->assertForbidden();
        $this->postJson(route('admin.sistema.usuarios.store'), ['name' => 'Persona Prueba', 'email' => 'prueba@example.com', 'role' => 'Docente'])->assertForbidden();
        $this->putJson(route('admin.sistema.usuarios.update', $this->sa), ['name' => $this->sa->name, 'email' => 'new@example.com'])->assertForbidden();
        foreach (['bloquear', 'desbloquear', 'reenviar-activacion'] as $operation) {
            $this->postJson(route('admin.sistema.usuarios.'.$operation, $this->sa), ['motivo' => 'Intento no autorizado'])->assertForbidden();
        }
    }

    public static function deniedRoles(): array
    {
        return [['Docente'], ['Estudiante'], [null]];
    }

    #[DataProvider('deniedRoles')]
    public function test_non_managers_are_denied_even_with_direct_module_permissions(?string $role): void
    {
        $user = User::factory()->active()->create();
        if ($role) {
            $user->assignRole($role);
        }
        $user->givePermissionTo(PermisosOrganizacion::ALL);
        $cargo = Cargo::factory()->create();
        $person = PersonalInstitucional::factory()->create();
        $this->actingAs($user);
        foreach (['cargos.index', 'personal.index'] as $route) {
            $this->get($this->url($route))->assertForbidden();
        }
        $this->postJson($this->url('cargos.store'), ['nombre_cargo' => 'Contador'])->assertForbidden();
        $this->putJson($this->url('cargos.update', $cargo), ['nombre_cargo' => 'Director'])->assertForbidden();
        $this->patchJson($this->url('cargos.estado', $cargo), ['estado' => 'inactivo'])->assertForbidden();
        $this->postJson($this->url('personal.store'), $this->person())->assertForbidden();
        $this->putJson($this->url('personal.update', $person), $this->person())->assertForbidden();
        $this->patchJson($this->url('personal.estado', $person), ['estado' => 'inactivo'])->assertForbidden();
        $this->assertFalse($user->canManageOrganization('personal.ver'));
    }

    public function test_pending_and_blocked_account_rules_precede_organization_permissions(): void
    {
        foreach ([User::factory()->pending()->create(), User::factory()->blocked()->create()] as $user) {
            $user->assignRole('Administrador');
            foreach (['cargos.index', 'personal.index'] as $route) {
                $this->actingAs($user)->get($this->url($route))->assertRedirect($user->estado_cuenta === EstadoCuenta::BLOQUEADA ? route('login') : route('verification.notice'));
            }
        }
    }

    public function test_sa_gate_works_and_edit_permission_does_not_grant_state_changes(): void
    {
        $this->actingAs($this->sa)->get($this->url('cargos.index'))->assertOk();
        $this->postJson($this->url('personal.store'), $this->person())->assertRedirect();
        $record = PersonalInstitucional::firstOrFail();
        Role::findByName('Administrador')->revokePermissionTo('personal.cambiar_estado');
        $this->actingAs($this->admin)->putJson($this->url('personal.update', $record), $this->person())->assertRedirect();
        $this->patchJson($this->url('personal.estado', $record), ['estado' => 'activo'])->assertForbidden();
        $this->putJson($this->url('personal.update', $record), $this->person(['estado' => 'activo']))->assertUnprocessable();
    }

    public function test_invalid_states_and_no_physical_deletion_routes(): void
    {
        $cargo = Cargo::factory()->create();
        $person = PersonalInstitucional::factory()->create();
        foreach (['desconocido', null, true, ['activo']] as $state) {
            $this->patchJson($this->url('cargos.estado', $cargo), ['estado' => $state])->assertUnprocessable();
            $this->patchJson($this->url('personal.estado', $person), ['estado' => $state])->assertUnprocessable();
        }
        $this->actingAs($this->sa)->deleteJson($this->url('personal.update', $person))->assertStatus(405);
        $this->deleteJson($this->url('cargos.update', $cargo))->assertStatus(405);
        $this->assertDatabaseCount('cargos', 1);
        $this->assertDatabaseCount('personal_institucional', 1);
    }

    public function test_database_constraints_nullable_unique_foreign_keys_and_restrict(): void
    {
        $cargo = Cargo::factory()->create(['nombre_cargo' => 'Docente']);
        $person = PersonalInstitucional::factory()->create(['user_id' => $this->admin->id, 'cargo_id' => $cargo->id_cargo, 'ci' => '1234567']);
        foreach ([
            fn () => DB::table('cargos')->insert(['nombre_cargo' => 'docente']),
            fn () => DB::table('cargos')->where('id_cargo', $cargo->id_cargo)->update(['estado' => 'pendiente']),
            fn () => DB::table('personal_institucional')->where('id_personal', $person->id_personal)->update(['estado' => 'bloqueada']),
            fn () => DB::table('personal_institucional')->where('id_personal', $person->id_personal)->update(['estado' => null]),
            fn () => DB::table('personal_institucional')->where('id_personal', $person->id_personal)->update(['user_id' => 999999]),
            fn () => DB::table('personal_institucional')->where('id_personal', $person->id_personal)->update(['cargo_id' => 999999]),
            fn () => PersonalInstitucional::factory()->create(['user_id' => $this->admin->id]),
            fn () => PersonalInstitucional::factory()->create(['ci' => '1234567']),
            fn () => DB::table('users')->where('id', $this->admin->id)->delete(),
            fn () => DB::table('cargos')->where('id_cargo', $cargo->id_cargo)->delete(),
        ] as $probe) {
            try {
                DB::transaction($probe);
                $this->fail('La restricción debe rechazar esta operación.');
            } catch (QueryException $exception) {
                $this->assertContains((string) $exception->getCode(), ['23000', '23505', '23514', '23502', '23503']);
            }
        }
        PersonalInstitucional::factory()->count(2)->create();
        $this->assertSame(2, PersonalInstitucional::whereNull('user_id')->whereNull('cargo_id')->whereNull('ci')->count());
    }
}
