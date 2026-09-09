<?php

namespace Tests\Feature\Validation;

use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthInputValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_establishment_preserves_password_spaces(): void
    {
        $password = '  Password123  ';
        $user = User::factory()->pending()->create(['email' => 'usuario+test@example.com']);
        $token = Password::broker()->createToken($user);
        $this->postJson('/reset-password', ['token' => $token, 'email' => ' USUARIO+TEST@EXAMPLE.COM ', 'password' => $password, 'password_confirmation' => $password])->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::check(trim($password), $user->password));
    }

    public function test_registration_and_reset_reject_malformed_credentials(): void
    {
        $this->postJson('/register', ['name' => 'Vicente123', 'email' => 'vicente@gmail', 'password' => ['bad'], 'password_confirmation' => ['bad']])->assertNotFound();
        $this->postJson('/forgot-password', ['email' => ['bad']])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/reset-password', ['token' => ['bad'], 'email' => 'correo@', 'password' => 'short', 'password_confirmation' => 'different'])->assertUnprocessable()->assertJsonValidationErrors(['token', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_email_normalization_and_boolean_remember(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->postJson('/login', ['email' => ' TEST@EXAMPLE.COM ', 'password' => 'password', 'remember' => 'yes'])->assertUnprocessable()->assertJsonValidationErrors('remember');
        $this->postJson('/login', ['email' => ' TEST@EXAMPLE.COM ', 'password' => 'password', 'remember' => true])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_profile_normalization_and_password_shapes(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->actingAs($user)->patchJson('/profile', ['name' => '  José   Álvarez '])->assertRedirect();
        $this->assertSame('José Álvarez', $user->fresh()->name);
        $this->patchJson('/profile', ['name' => 'Vicente123', 'email' => 'test@example.com'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->putJson('/password', ['current_password' => ['bad'], 'password' => ['bad'], 'password_confirmation' => ['bad']])->assertUnprocessable()->assertJsonValidationErrors(['current_password', 'password', 'password_confirmation']);
        $this->postJson('/confirm-password', ['password' => ['bad']])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->deleteJson('/profile', ['password' => ['bad']])->assertMethodNotAllowed();
        $this->assertNotNull($user->fresh());
    }

    public function test_sa_create_update_keep_normalization_unique_email_and_do_not_set_passwords(): void
    {
        $this->seed(RolesAndUsersSeeder::class);
        Notification::fake();
        $this->actingAs(User::role('Super Administrador')->firstOrFail());
        $data = ['name' => '  José   Muñoz ', 'email' => ' NUEVO@EXAMPLE.COM ', 'role' => 'Estudiante'];
        $this->postJson(route('admin.sistema.usuarios.store'), $data)->assertRedirect();
        $user = User::where('email', 'nuevo@example.com')->firstOrFail();
        $hash = $user->password;
        $this->assertSame('José Muñoz', $user->name);
        $this->postJson(route('admin.sistema.usuarios.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->putJson(route('admin.sistema.usuarios.update', $user), array_replace($data, ['password' => '', 'password_confirmation' => '']))->assertRedirect();
        $this->assertSame($hash, $user->fresh()->password);
        $this->putJson(route('admin.sistema.usuarios.update', $user), array_replace($data, ['name' => 'Vicente123']))->assertUnprocessable()->assertJsonValidationErrors('name');
    }
}
