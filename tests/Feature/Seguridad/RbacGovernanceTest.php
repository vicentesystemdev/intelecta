<?php

namespace Tests\Feature\Seguridad;

use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Postulantes\Actions\VincularUsuarioPostulanteAction;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Services\CuentaService;
use App\Domains\Seguridad\Services\DesplegarMatrizRbac;
use App\Domains\Seguridad\Services\PermisosRolService;
use App\Domains\Seguridad\Support\MatrizRbac;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RbacGovernanceTest extends TestCase
{
    use RefreshDatabase;

    private User $sa;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (MatrizRbac::CATALOG as $name) {
            Permission::findOrCreate($name, 'web');
        }
        foreach (MatrizRbac::ROLES as $name) {
            Role::findOrCreate($name, 'web')->syncPermissions(MatrizRbac::forRole($name));
        }
        $this->sa = User::factory()->active()->create()->assignRole('Super Administrador');
        $this->actingAs($this->sa);
    }

    private function rolesUrl(User $user): string
    {
        return route('admin.sistema.usuarios.roles', $user);
    }

    private function staff(User $user): TutorAcademico
    {
        $person = PersonalInstitucional::factory()->pending()->create(['user_id' => $user->id]);

        return TutorAcademico::factory()->create(['personal_id' => $person->id_personal]);
    }

    public function test_multirole_assignment_add_remove_and_generic_edits_preserve_other_roles(): void
    {
        $user = User::factory()->active()->create();
        $tutor = $this->staff($user);
        $this->putJson($this->rolesUrl($user), ['roles' => ['Administrador']])->assertRedirect();
        $this->putJson($this->rolesUrl($user), ['roles' => ['Docente', 'Administrador']])->assertRedirect();
        $this->assertSame(['Administrador', 'Docente'], $user->fresh()->getRoleNames()->sort()->values()->all());
        $version = $user->fresh()->version_acceso;
        $this->putJson(route('admin.sistema.usuarios.update', $user), ['name' => 'Nombre Visual Nuevo', 'email' => $user->email])->assertRedirect();
        $this->putJson(route('admin.institucional.personal.update', $tutor->personal), ['nombres' => 'María Luz', 'apellidos' => 'Quispe Rojas', 'cargo_id' => null, 'ci' => null, 'celular' => null, 'correo_contacto' => null])->assertRedirect();
        $this->patchJson(route('admin.institucional.tutores.update', $tutor), ['personal_id' => $tutor->personal_id, 'estado_tutor' => 'activo', 'especialidad_tutor' => 'Física'])->assertRedirect();
        $this->assertSame($version, $user->fresh()->version_acceso);
        $this->assertSame(['Administrador', 'Docente'], $user->fresh()->getRoleNames()->sort()->values()->all());
        $this->putJson($this->rolesUrl($user), ['roles' => ['Docente']])->assertRedirect();
        $this->assertSame(['Docente'], $user->fresh()->getRoleNames()->all());
        $audit = DB::table('bitacora_sistema')->where('accion', 'asignar_roles')->orderByDesc('id_bitacora')->first();
        $this->assertSame(['Administrador', 'Docente'], json_decode($audit->valores_anteriores, true)['roles']);
        $this->assertSame(['Docente'], json_decode($audit->valores_nuevos, true)['roles']);
        $this->assertSame($this->sa->id, $audit->user_id);
        $this->assertDatabaseCount('model_has_permissions', 0);
    }

    public static function malformedRoles(): array
    {
        return [[[]], [['roles' => 'Docente']], [['roles' => null]], [['roles' => ['Docente', 'Docente']]],
            [['roles' => [1]]], [['roles' => [true]]], [['roles' => [['Docente']]]], [['roles' => ['No existe']]],
            [['roles' => ['Postulante']]], [['roles' => ['x' => 'Docente']]], [['roles' => ['Estudiante', 'Docente']]],
            [['roles' => ['Estudiante', 'Administrador']]], [['roles' => ['Super Administrador', 'Docente']]],
            [['roles' => [], 'role' => null]]];
    }

    #[DataProvider('malformedRoles')]
    public function test_invalid_role_payloads_do_not_change_assignments(array $payload): void
    {
        $user = User::factory()->active()->create();
        $this->putJson($this->rolesUrl($user), $payload)->assertUnprocessable();
        $this->assertCount(0, $user->fresh()->roles);
        $this->assertDatabaseCount('personal_institucional', 0);
        $this->assertDatabaseCount('postulantes', 0);
    }

    public function test_generic_create_and_update_reject_even_empty_role_keys(): void
    {
        $user = User::factory()->active()->create()->assignRole(['Administrador', 'Docente']);
        foreach (['role' => null, 'roles' => [], 'roles_nonempty' => ['Docente']] as $key => $value) {
            $key = $key === 'roles_nonempty' ? 'roles' : $key;
            $data = ['name' => 'Nombre Visual', 'email' => 'otra@example.com', $key => $value];
            $this->putJson(route('admin.sistema.usuarios.update', $user), $data)->assertUnprocessable()->assertJsonValidationErrors($key);
            $this->postJson(route('admin.sistema.usuarios.store'), $data)->assertUnprocessable()->assertJsonValidationErrors($key);
        }
        $this->assertCount(2, $user->fresh()->roles);
    }

    public function test_service_cannot_be_used_to_smuggle_roles_in_generic_edit(): void
    {
        $this->expectException(ValidationException::class);
        app(CuentaService::class)->update($this->sa, $this->sa->id, ['name' => 'Responsable', 'email' => $this->sa->email, 'roles' => []]);
    }

    public function test_new_assignments_require_profiles_but_pending_personal_is_valid(): void
    {
        $user = User::factory()->active()->create();
        foreach (['Estudiante', 'Docente', 'Administrador'] as $role) {
            $this->putJson($this->rolesUrl($user), ['roles' => [$role]])->assertUnprocessable()->assertJsonValidationErrors('roles');
        }
        $this->assertDatabaseCount('personal_institucional', 0);
        $tutor = $this->staff($user);
        $this->putJson($this->rolesUrl($user), ['roles' => ['Administrador', 'Docente']])->assertRedirect();
        $this->assertSame('pendiente', $tutor->personal->fresh()->estado->value);
        $this->assertSame('activo', $tutor->fresh()->estado_tutor);
        $this->assertNull($tutor->personal->cargo_id);
    }

    public function test_roleless_account_can_be_explicitly_accredited_then_assigned_student(): void
    {
        $user = app(CuentaService::class)->create($this->sa, ['name' => 'Cuenta Estudiantil', 'email' => 'acreditar@example.com']);
        $post = Postulante::factory()->create();
        app(VincularUsuarioPostulanteAction::class)->execute($user->id, $post->id_post, $this->sa, 'Identidad acreditada documentalmente');
        $this->assertCount(0, $user->fresh()->roles);
        $this->putJson($this->rolesUrl($user), ['roles' => ['Estudiante']])->assertRedirect();
        $this->assertSame($post->id_post, $user->fresh()->postulante->id_post);
        $this->assertFalse($user->fresh()->cuentaActiva());
        $this->assertDatabaseCount('postulantes', 1);
    }

    public function test_archived_profiles_are_not_eligible_for_new_roles(): void
    {
        $user = User::factory()->active()->create();
        Postulante::factory()->withUser($user)->create()->delete();
        $this->putJson($this->rolesUrl($user), ['roles' => ['Estudiante']])->assertUnprocessable();
        $this->staff($user)->delete();
        $this->putJson($this->rolesUrl($user), ['roles' => ['Docente']])->assertUnprocessable();
    }

    public function test_legacy_admin_and_student_keep_existing_roles_without_creating_profiles(): void
    {
        foreach (['Administrador', 'Estudiante'] as $role) {
            $user = User::factory()->active()->create()->assignRole($role);
            $this->putJson($this->rolesUrl($user), ['roles' => [$role]])->assertRedirect();
            $this->assertTrue($user->fresh()->hasRole($role));
        }
        $this->assertDatabaseCount('personal_institucional', 0);
        $this->assertDatabaseCount('postulantes', 0);
    }

    public function test_super_admin_needs_no_profile_and_last_sa_cannot_be_replaced_with_multiple_roles(): void
    {
        $this->putJson($this->rolesUrl($this->sa), ['roles' => ['Administrador', 'Docente']])->assertUnprocessable()->assertJsonValidationErrors('ultimo_sa');
        $other = User::factory()->active()->create();
        $this->putJson($this->rolesUrl($other), ['roles' => ['Super Administrador']])->assertRedirect();
        $this->putJson($this->rolesUrl($other), ['roles' => []])->assertRedirect();
        $this->assertTrue($this->sa->fresh()->hasRole('Super Administrador'));
        $this->assertDatabaseCount('personal_institucional', 0);
    }

    public function test_guard_mismatch_is_rejected(): void
    {
        Role::findByName('Docente', 'web')->delete();
        Role::create(['name' => 'Docente', 'guard_name' => 'api']);
        $user = User::factory()->active()->create();
        $this->putJson($this->rolesUrl($user), ['roles' => ['Docente']])->assertUnprocessable();
    }

    public function test_security_is_sa_only_even_if_a_role_has_forged_security_permissions(): void
    {
        foreach (['Administrador', 'Docente', 'Estudiante'] as $name) {
            Role::findByName($name)->givePermissionTo(MatrizRbac::SECURITY);
            $actor = User::factory()->active()->create()->assignRole($name);
            $this->actingAs($actor)->getJson(route('admin.sistema.usuarios'))->assertForbidden();
            $this->getJson(route('admin.sistema.roles-permisos'))->assertForbidden();
            $this->putJson($this->rolesUrl($actor), ['roles' => ['Super Administrador']])->assertForbidden();
            $this->postJson(route('admin.sistema.usuarios.store'), ['name' => 'Cuenta Prueba', 'email' => 'denegada@example.com'])->assertForbidden();
            $this->putJson(route('admin.sistema.usuarios.update', $actor), ['name' => 'Cuenta Prueba', 'email' => 'denegada@example.com'])->assertForbidden();
            foreach (['bloquear', 'desbloquear', 'reenviar-activacion'] as $operation) {
                $this->postJson(route('admin.sistema.usuarios.'.$operation, $actor), ['motivo' => 'Intento de escalamiento de prueba'])->assertForbidden();
            }
            $this->putJson(route('admin.sistema.roles-permisos.update', Role::findByName($name)), ['permissions' => MatrizRbac::SECURITY])->assertForbidden();
            $this->assertFalse($actor->fresh()->hasRole('Super Administrador'));
        }
    }

    public function test_role_assignment_refreshes_actor_authority_under_mutex(): void
    {
        $stale = $this->sa;
        $stale->load('roles');
        $this->sa->fresh()->syncRoles([]);
        $this->expectException(AuthorizationException::class);
        app(CuentaService::class)->assignRoles($stale, User::factory()->active()->create()->id, ['roles' => ['Super Administrador']]);
    }

    public function test_teacher_keeps_global_sensitive_modules_closed_and_scoped_modules_fail_empty(): void
    {
        $teacher = User::factory()->active()->create()->assignRole('Docente');
        $this->staff($teacher);
        $this->actingAs($teacher);
        foreach (['/dashboard', '/admin/institucional/programas', '/admin/institucional/ranking', '/admin/institucional/simulacros', '/reportes-academicos', '/reportes-academicos/pdf/rendimiento', '/admin/institucional/matriculas-cuotas', '/admin/analisis/riesgo-academico', '/admin/analisis/learning-analytics'] as $url) {
            $this->getJson($url)->assertForbidden();
        }
        foreach (['/postulantes', '/admin/institucional/ficha-academica', '/admin/evaluaciones/resultados', '/admin/institucional/asistencia', '/admin/institucional/grupos'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/preguntas', '/preguntas/crear', '/plantillas-evaluacion', '/areas-conocimiento', '/temas', '/admin/evaluaciones/materias'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_union_of_roles_is_additive_and_order_independent_in_authorization_and_login(): void
    {
        Role::findByName('Administrador')->syncPermissions(['dashboard.ver', 'postulantes.ver']);
        foreach ([['Administrador', 'Docente'], ['Docente', 'Administrador']] as $roles) {
            $user = User::factory()->active()->create()->assignRole($roles);
            $expected = array_unique([...MatrizRbac::TEACHER, 'dashboard.ver', 'postulantes.ver']);
            $this->assertEqualsCanonicalizing($expected, $user->getAllPermissions()->pluck('name')->all());
            $this->assertSame(route('dashboard'), $user->homeRoute());
            $this->actingAs($user)->get('/preguntas')->assertOk()->assertInertia(fn (Assert $page) => $page->where('auth.roles', ['Administrador', 'Docente'])->where('auth.security', false));
            $this->get('/postulantes')->assertOk();
            $this->assertSame('Administrador + Docente', $user->rolesLabel());
        }
    }

    public function test_positive_context_does_not_reject_a_legacy_mixed_student_admin(): void
    {
        $user = User::factory()->active()->create()->assignRole(['Estudiante', 'Administrador']);
        $this->actingAs($user)->get('/admin/institucional/cargos')->assertOk();
        $this->get('/dashboard')->assertOk();
        $this->assertSame(route('dashboard'), $user->homeRoute());
    }

    public function test_roleless_account_with_direct_permission_cannot_enter_administrative_context(): void
    {
        // Deliberately corrupted fixture proves positive context, never production delegation.
        $user = User::factory()->active()->create()->givePermissionTo('preguntas.ver');
        $this->actingAs($user)->getJson('/preguntas')->assertForbidden();
    }

    public static function homes(): array
    {
        return [[['Super Administrador'], 'dashboard'], [['Administrador'], 'dashboard'], [['Docente'], 'preguntas.index'], [['Estudiante'], 'estudiante.evaluaciones'], [['Docente', 'Administrador'], 'dashboard'], [['Administrador', 'Docente'], 'dashboard']];
    }

    #[DataProvider('homes')]
    public function test_login_has_deterministic_context_priority(array $roles, string $home): void
    {
        auth()->logout();
        $user = User::factory()->active()->create()->assignRole($roles);
        $this->withSession(['url.intended' => route('admin.sistema.usuarios')])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route($home));
    }

    public function test_teacher_without_any_capability_has_a_safe_home(): void
    {
        Role::findByName('Docente')->syncPermissions([]);
        $user = User::factory()->active()->create()->assignRole('Docente');
        $this->assertSame(url('/'), $user->homeRoute());
    }

    public function test_landing_receives_the_same_positive_context_as_login(): void
    {
        foreach ([['Docente'], ['Estudiante', 'Administrador']] as $roles) {
            $user = User::factory()->active()->create()->assignRole($roles);
            $context = in_array('Administrador', $roles, true) ? 'academic' : 'teacher';
            $this->actingAs($user)->get('/')->assertInertia(fn (Assert $page) => $page
                ->where('auth.context', $context)->where('auth.homeUrl', $user->homeRoute()));
        }
    }

    public function test_every_active_capability_has_a_route_consumer_and_no_negative_rules_exist(): void
    {
        $middleware = collect(app('router')->getRoutes())->flatMap(fn ($route) => $route->gatherMiddleware())->implode(' ');
        foreach (MatrizRbac::active() as $permission) {
            $this->assertStringContainsString(':'.$permission, $middleware, $permission);
        }
        $this->assertDatabaseCount('model_has_permissions', 0);
    }

    public function test_permission_editor_enforces_ceilings_and_audits_before_after(): void
    {
        foreach (['Administrador' => ['usuarios.crear'], 'Docente' => ['asistencia.editar'], 'Estudiante' => ['preguntas.ver'], 'Super Administrador' => []] as $name => $permissions) {
            $role = Role::findByName($name);
            $this->putJson(route('admin.sistema.roles-permisos.update', $role), ['permissions' => $permissions])->assertUnprocessable();
            $this->assertEqualsCanonicalizing(MatrizRbac::forRole($name), $role->fresh()->permissions->pluck('name')->all());
        }
        $teacher = Role::findByName('Docente');
        $this->putJson(route('admin.sistema.roles-permisos.update', $teacher), ['permissions' => ['preguntas.ver']])->assertRedirect();
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'actualizar_permisos', 'user_id' => $this->sa->id, 'entidad_id' => (string) $teacher->id]);
        $this->assertSame(['preguntas.ver'], $teacher->fresh()->permissions->pluck('name')->all());
    }

    public function test_inactive_sa_cannot_use_security_service_or_http(): void
    {
        $actor = User::factory()->pending()->create()->assignRole('Super Administrador');
        $this->actingAs($actor)->getJson(route('admin.sistema.roles-permisos'))->assertForbidden();
        try {
            app(PermisosRolService::class)->update($actor, Role::findByName('Docente')->id, []);
            $this->fail('Debe exigir SA activo.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_retired_permissions_have_no_route_consumers_and_active_matrix_is_exact(): void
    {
        $middleware = collect(app('router')->getRoutes())->flatMap(fn ($route) => $route->gatherMiddleware())->implode(' ');
        foreach (MatrizRbac::RETIRED as $name) {
            $this->assertStringNotContainsString('permission:'.$name, $middleware);
            $this->assertFalse(DB::table('role_has_permissions')->where('permission_id', Permission::findByName($name)->id)->exists());
        }
        $service = app(DesplegarMatrizRbac::class);
        $this->assertSame($service->target(), $service->matrix($service->state()));
        $this->assertDatabaseCount('role_has_permissions', 146);
        $this->assertDatabaseCount('model_has_permissions', 0);
        $this->assertDatabaseCount('roles', 4);
    }
}
