<?php

namespace Tests\Feature\Portal;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Services\RevocarAccesoService;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StableStudentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndUsersSeeder::class);
    }

    private function assertPortalDenied(User $user): void
    {
        foreach (['estudiante.evaluaciones', 'estudiante.ficha', 'estudiante.ranking'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
        // No data validation or academic queries should run for these requests.
        $this->postJson('/estudiante/evaluaciones/1/iniciar')->assertForbidden();
        $this->postJson('/estudiante/evaluaciones/1/enviar')->assertForbidden();
    }

    public function test_student_without_fk_is_denied_even_when_email_matches(): void
    {
        $user = User::role('Estudiante')->firstOrFail();
        Postulante::factory()->create(['email_post' => $user->email]);
        $this->assertPortalDenied($user);
        $this->getJson(route('estudiante.ficha'))->assertForbidden()
            ->assertJsonPath('message', 'Tu cuenta todavía no está vinculada a un expediente académico disponible. Consulta con administración académica.');
    }

    public function test_roleless_teacher_admin_and_super_admin_do_not_gain_student_access_even_with_fk(): void
    {
        $users = [User::factory()->create(), User::role('Docente')->firstOrFail(), User::role('Administrador')->firstOrFail(), User::role('Super Administrador')->firstOrFail()];
        foreach ($users as $user) {
            Postulante::factory()->withUser($user)->create();
            $this->assertPortalDenied($user);
        }
    }

    public function test_public_registration_cannot_claim_a_contact_email(): void
    {
        Postulante::factory()->create(['email_post' => 'coincidencia@example.com']);
        $this->post('/register', ['name' => 'Nueva Persona', 'email' => 'coincidencia@example.com', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertNotFound();
        $this->assertDatabaseMissing('users', ['email' => 'coincidencia@example.com']);
        $user = User::factory()->active()->create(['email' => 'coincidencia@example.com']);
        $this->assertPortalDenied($user);
        $this->assertNull($user->postulante);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['estudiante.evaluaciones', 'estudiante.ficha', 'estudiante.ranking'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_archived_link_cannot_access_the_portal(): void
    {
        $user = User::role('Estudiante')->firstOrFail();
        Postulante::factory()->withUser($user)->create()->delete();
        $this->assertPortalDenied($user);
    }

    public function test_contact_and_authorized_login_email_changes_preserve_identity(): void
    {
        $user = User::role('Estudiante')->firstOrFail();
        $postulante = Postulante::factory()->withUser($user)->create();
        $other = Postulante::factory()->create(['email_post' => $user->email]);
        $assertOwnRecord = function () use ($user, $postulante, $other): void {
            $this->actingAs($user->fresh())->get(route('estudiante.ficha', ['id_post' => $other->id_post]))->assertOk()
                ->assertInertia(fn (Assert $page) => $page->where('postulante.id_post', $postulante->id_post));
            $this->get(route('estudiante.evaluaciones'))->assertOk();
            $this->get(route('estudiante.ranking'))->assertOk();
        };
        $assertOwnRecord();
        $postulante->update(['email_post' => 'otro.contacto@example.com']);
        $assertOwnRecord();
        $sa = User::role('Super Administrador')->firstOrFail();
        $this->actingAs($sa)->putJson(route('admin.sistema.usuarios.update', $user), [
            'name' => $user->name, 'email' => ' NUEVO.ACCESO@EXAMPLE.COM ', 'role' => 'Estudiante',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('nuevo.acceso@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(EstadoCuenta::PENDIENTE, $user->fresh()->estado_cuenta);
        $user->refresh()->markEmailAsVerified();
        $this->withSession([RevocarAccesoService::SESSION_KEY => $user->version_acceso]);
        $assertOwnRecord();
        $this->assertSame($user->id, $postulante->fresh()->user_id);
        $this->assertNull($other->fresh()->user_id);
    }
}
