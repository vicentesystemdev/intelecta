<?php

namespace Tests\Feature\Auth;

use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class LoginEmailGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndUsersSeeder::class);
    }

    public function test_every_role_is_unable_to_change_login_email_via_profile_http(): void
    {
        foreach (['Estudiante', 'Docente', 'Administrador', 'Super Administrador'] as $role) {
            $user = User::role($role)->firstOrFail();
            $oldEmail = $user->email;
            $this->actingAs($user)->patchJson('/profile', ['name' => 'Nombre Permitido', 'email' => 'manipulado@example.com'])
                ->assertUnprocessable()->assertJsonValidationErrors('email');
            $this->assertSame($oldEmail, $user->fresh()->email);
            $this->patchJson('/profile', ['name' => 'Nombre Permitido'])->assertRedirect();
            $this->assertSame('Nombre Permitido', $user->fresh()->name);
            $this->assertNotNull($user->fresh()->email_verified_at);
        }
    }

    public function test_admin_cannot_change_email_or_other_identity_fields(): void
    {
        $admin = User::role('Administrador')->firstOrFail();
        $user = User::role('Estudiante')->firstOrFail();
        $data = ['name' => 'Nombre Permitido', 'email' => 'no.autorizado@example.com'];
        $oldEmail = $user->email;
        $this->actingAs($admin)->putJson(route('admin.sistema.usuarios.update', $user), $data)->assertForbidden();
        $this->assertSame($oldEmail, $user->fresh()->email);
        $this->assertNotSame('Nombre Permitido', $user->fresh()->name);
        $data['email'] = $oldEmail;
        $this->putJson(route('admin.sistema.usuarios.update', $user), $data)->assertForbidden();
        $this->assertNotSame('Nombre Permitido', $user->fresh()->name);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_sa_email_change_validates_and_invalidates_recovery_verification_and_database_sessions(): void
    {
        $sa = User::role('Super Administrador')->firstOrFail();
        $user = User::factory()->create(['name' => 'Persona Prueba', 'email' => 'acceso.anterior@example.com']);
        $user->assignRole('Estudiante');
        $postulante = Postulante::factory()->withUser($user)->create();
        $oldEmail = $user->email;
        $oldRemember = $user->remember_token;
        $reset = Password::broker()->createToken($user);
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert(['id' => 'identity-session-test', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        $data = ['name' => $user->name, 'email' => 'malformado@'];
        $url = route('admin.sistema.usuarios.update', $user);
        $this->actingAs($sa)->putJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('email');
        $data['email'] = $sa->email;
        $this->putJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('email');
        $data['email'] = ' NUEVO.ACCESO@EXAMPLE.COM ';
        $this->putJson($url, $data)->assertRedirect();
        $user->refresh();
        $this->assertSame('nuevo.acceso@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertNotSame($oldRemember, $user->remember_token);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $oldEmail]);
        $this->assertDatabaseMissing('sessions', ['id' => 'identity-session-test']);
        $this->assertFalse(Password::broker()->tokenExists($user, $reset));
        $this->assertSame($postulante->id_post, $user->postulante->id_post);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'cambiar_correo_acceso', 'entidad' => 'users', 'entidad_id' => (string) $user->id]);
        $this->post('/logout');
        $this->post('/login', ['email' => $oldEmail, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'nuevo.acceso@example.com', 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
