<?php

namespace Tests\Feature\Auth;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Services\CuentaService;
use App\Domains\Seguridad\Services\RevocarAccesoService;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $sa;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        foreach (['Super Administrador', 'Administrador', 'Docente', 'Estudiante'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        $this->sa = User::factory()->active()->create()->assignRole('Super Administrador');
    }

    private function accountUrl(User $user, string $operation): string
    {
        return route('admin.sistema.usuarios.'.$operation, $user);
    }

    public function test_sa_creates_pending_account_with_unknown_password_and_native_invitation(): void
    {
        $this->actingAs($this->sa)->postJson(route('admin.sistema.usuarios.store'), [
            'name' => ' Persona   Invitada ', 'email' => ' INVITADA@EXAMPLE.COM ', 'role' => 'Estudiante',
        ])->assertRedirect();
        $user = User::where('email', 'invitada@example.com')->firstOrFail();
        $this->assertSame(EstadoCuenta::PENDIENTE, $user->estado_cuenta);
        $this->assertNull($user->email_verified_at);
        $this->assertFalse(Hash::check('password', $user->password));
        $this->assertAuthenticatedAs($this->sa);
        $this->assertNull($user->postulante);
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'crear', 'entidad_id' => (string) $user->id]);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'solicitar_activacion', 'entidad_id' => (string) $user->id]);
    }

    public function test_sa_cannot_supply_password_verified_flag_or_account_state(): void
    {
        $this->actingAs($this->sa)->postJson(route('admin.sistema.usuarios.store'), [
            'name' => 'Persona Invitada', 'email' => 'invitada@example.com', 'role' => 'Estudiante',
            'password' => 'Conocida123', 'email_verified_at' => now(), 'estado_cuenta' => 'activa',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password', 'email_verified_at', 'estado_cuenta']);
        $this->assertDatabaseCount('users', 1);
        Notification::assertNothingSent();
    }

    public function test_native_invitation_is_single_use_and_does_not_activate_or_link(): void
    {
        $user = app(CuentaService::class)->create($this->sa, ['name' => 'Persona Invitada', 'email' => 'invitada@example.com', 'role' => 'Estudiante']);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $data = ['email' => $user->email, 'token' => $token, 'password' => '  Password123  ', 'password_confirmation' => '  Password123  '];
        $this->postJson('/reset-password', $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password));
        $this->assertSame(EstadoCuenta::PENDIENTE, $user->fresh()->estado_cuenta);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->postulante);
        $this->assertSame(['Estudiante'], $user->fresh()->getRoleNames()->all());
        $this->postJson('/reset-password', $data)->assertUnprocessable();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_resend_replaces_token_and_admin_cannot_resend(): void
    {
        $user = User::factory()->pending()->create();
        $oldToken = Password::broker()->createToken($user);
        $this->actingAs($this->sa)->postJson($this->accountUrl($user, 'reenviar-activacion'))->assertRedirect();
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $this->assertFalse(Password::broker()->tokenExists($user, $oldToken));
        $this->assertTrue(Password::broker()->tokenExists($user, $token));
    }

    public function test_blocked_cannot_login_and_pending_cannot_use_private_modules(): void
    {
        $blocked = User::factory()->blocked()->create();
        $this->postJson('/login', ['email' => $blocked->email, 'password' => 'password'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
        $pending = User::factory()->pending()->create()->assignRole('Super Administrador');
        $this->post('/login', ['email' => $pending->email, 'password' => 'password'])->assertRedirect(route('verification.notice'));
        foreach (['dashboard', 'admin.sistema.usuarios', 'estudiante.evaluaciones', 'postulantes.index', 'reportes-academicos.index', 'profile.edit'] as $name) {
            $this->getJson(route($name))->assertForbidden();
        }
        $this->get('/verify-email')->assertOk();
        $this->post('/email/verification-notification')->assertRedirect();
        $this->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_verification_activates_pending_and_never_unblocks(): void
    {
        $user = User::factory()->pending()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect();
        $this->assertSame(EstadoCuenta::ACTIVA, $user->fresh()->estado_cuenta);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'activar_cuenta', 'entidad_id' => (string) $user->id]);
        $blocked = User::factory()->pending()->blocked()->create();
        $blocked->markEmailAsVerified();
        $this->assertNotNull($blocked->fresh()->email_verified_at);
        $this->assertSame(EstadoCuenta::BLOQUEADA, $blocked->fresh()->estado_cuenta);
    }

    public function test_block_and_unblock_preserve_profiles_roles_and_verification_semantics(): void
    {
        foreach ([true, false] as $verified) {
            $user = ($verified ? User::factory()->active() : User::factory()->pending())->create()->assignRole('Estudiante');
            $postulante = Postulante::factory()->withUser($user)->create();
            $this->actingAs($this->sa)->postJson($this->accountUrl($user, 'bloquear'), ['motivo' => 'Suspensión documentada de prueba'])->assertRedirect();
            $this->assertSame(EstadoCuenta::BLOQUEADA, $user->fresh()->estado_cuenta);
            $this->assertSame($user->id, $postulante->fresh()->user_id);
            $this->assertTrue($user->fresh()->hasRole('Estudiante'));
            $this->postJson($this->accountUrl($user, 'desbloquear'))->assertRedirect();
            $this->assertSame($verified ? EstadoCuenta::ACTIVA : EstadoCuenta::PENDIENTE, $user->fresh()->estado_cuenta);
        }
    }

    public function test_old_session_is_rejected_even_after_unlock_with_non_database_driver(): void
    {
        config(['session.driver' => 'array']);
        $user = User::factory()->active()->create();
        $oldRemember = $user->remember_token;
        $this->actingAs($user)->withSession([RevocarAccesoService::SESSION_KEY => 0])->get('/profile')->assertOk();
        app(CuentaService::class)->block($this->sa, $user->id, 'Bloqueo por seguridad de prueba');
        app(CuentaService::class)->unblock($this->sa, $user->id);
        $this->assertNotSame($oldRemember, $user->fresh()->remember_token);
        $this->getJson('/profile')->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_block_revokes_database_sessions_and_recovery_tokens(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->active()->create();
        $token = Password::broker()->createToken($user);
        DB::table('sessions')->insert(['id' => 'account-old-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        app(CuentaService::class)->block($this->sa, $user->id, 'Bloqueo de seguridad verificado');
        $this->assertDatabaseMissing('sessions', ['id' => 'account-old-session']);
        $this->assertFalse(Password::broker()->tokenExists($user->fresh(), $token));
        $this->actingAs($user)->getJson('/profile')->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_reset_does_not_unblock_assign_roles_or_link(): void
    {
        $user = User::factory()->blocked()->create();
        $token = Password::broker()->createToken($user);
        $email = $user->email;
        $this->post('/reset-password', ['email' => $email, 'token' => $token, 'password' => 'Password456', 'password_confirmation' => 'Password456'])->assertRedirect();
        $this->assertSame(EstadoCuenta::BLOQUEADA, $user->fresh()->estado_cuenta);
        $this->assertSame($email, $user->fresh()->email);
        $this->assertCount(0, $user->fresh()->roles);
        $this->assertNull($user->fresh()->postulante);
        $this->postJson('/login', ['email' => $email, 'password' => 'Password456'])->assertUnprocessable();
    }

    public function test_ordinary_admin_cannot_perform_any_identity_security_operation(): void
    {
        $admin = User::factory()->active()->create()->assignRole('Administrador');
        $user = User::factory()->pending()->create()->assignRole('Estudiante');
        $data = ['name' => 'Persona Prueba', 'email' => 'persona@example.com', 'role' => 'Super Administrador'];
        $this->actingAs($admin)->postJson(route('admin.sistema.usuarios.store'), $data)->assertForbidden();
        $this->putJson($this->accountUrl($user, 'update'), $data)->assertForbidden();
        foreach (['bloquear', 'desbloquear', 'reenviar-activacion'] as $operation) {
            $this->postJson($this->accountUrl($user, $operation), ['motivo' => 'Intento no autorizado de prueba'])->assertForbidden();
        }
        Notification::assertNothingSent();
        $this->assertSame(EstadoCuenta::PENDIENTE, $user->fresh()->estado_cuenta);
        $this->assertTrue($user->fresh()->hasRole('Estudiante'));
    }

    public function test_last_active_sa_cannot_be_blocked_demoted_or_made_pending_by_email_change(): void
    {
        User::factory()->blocked()->create()->assignRole('Super Administrador');
        $this->actingAs($this->sa)->postJson($this->accountUrl($this->sa, 'bloquear'), ['motivo' => 'Bloqueo solicitado para prueba'])->assertUnprocessable()->assertJsonValidationErrors('ultimo_sa');
        $this->putJson($this->accountUrl($this->sa, 'update'), ['name' => 'Persona Responsable', 'email' => $this->sa->email, 'role' => 'Administrador'])
            ->assertUnprocessable()->assertJsonValidationErrors('ultimo_sa');
        $this->putJson($this->accountUrl($this->sa, 'update'), ['name' => 'Persona Responsable', 'email' => 'nuevo.sa@example.com', 'role' => 'Super Administrador'])
            ->assertUnprocessable()->assertJsonValidationErrors('ultimo_sa');
        $this->assertTrue($this->sa->fresh()->cuentaActiva());
        $this->assertTrue($this->sa->fresh()->hasRole('Super Administrador'));
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'rechazar_ultimo_sa']);
        $this->delete('/profile')->assertMethodNotAllowed();
        $this->assertNotNull($this->sa->fresh());
    }

    public function test_two_active_sa_allow_one_to_be_blocked_or_demoted(): void
    {
        $other = User::factory()->active()->create()->assignRole('Super Administrador');
        app(CuentaService::class)->block($this->sa, $other->id, 'Bloqueo de prueba documentado');
        $this->assertSame(EstadoCuenta::BLOQUEADA, $other->fresh()->estado_cuenta);
        app(CuentaService::class)->unblock($this->sa, $other->id);
        app(CuentaService::class)->update($this->sa, $other->id, ['name' => 'Persona Responsable', 'email' => $other->email, 'role' => 'Administrador']);
        $this->assertTrue($other->fresh()->hasRole('Administrador'));
        $this->assertTrue($this->sa->fresh()->cuentaActiva());
    }

    public function test_email_change_preserves_block_or_requires_new_verification_without_changing_profile(): void
    {
        foreach ([false, true] as $blocked) {
            $user = ($blocked ? User::factory()->blocked() : User::factory()->active())->create()->assignRole('Estudiante');
            $postulante = Postulante::factory()->withUser($user)->create();
            $oldEmail = $user->email;
            $token = Password::broker()->createToken($user);
            app(CuentaService::class)->update($this->sa, $user->id, ['name' => 'Persona Prueba', 'email' => 'nuevo.'.$user->id.'@example.com', 'role' => 'Estudiante']);
            $this->assertNull($user->fresh()->email_verified_at);
            $this->assertSame($blocked ? EstadoCuenta::BLOQUEADA : EstadoCuenta::PENDIENTE, $user->fresh()->estado_cuenta);
            $this->assertSame($postulante->id_post, $user->fresh()->postulante->id_post);
            $this->assertDatabaseMissing('password_reset_tokens', ['email' => $oldEmail]);
            $this->assertFalse(Password::broker()->tokenExists($user->fresh(), $token));
        }
    }

    public function test_factory_states_are_explicit(): void
    {
        $this->assertTrue(User::factory()->active()->create()->cuentaActiva());
        $this->assertSame(EstadoCuenta::PENDIENTE, User::factory()->pending()->create()->estado_cuenta);
        $this->assertSame(EstadoCuenta::BLOQUEADA, User::factory()->blocked()->create()->estado_cuenta);
    }

    public function test_old_remember_cookie_cannot_restore_access_after_block_and_unlock(): void
    {
        $user = User::factory()->active()->create();
        $recaller = Auth::guard()->getRecallerName();
        $oldCookie = $user->id.'|'.$user->remember_token.'|'.$user->password;
        app(CuentaService::class)->block($this->sa, $user->id, 'Revocación de recuerdo de prueba');
        app(CuentaService::class)->unblock($this->sa, $user->id);
        $this->withCookie($recaller, $oldCookie)->get('/profile')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_current_remember_cookie_can_initialize_a_new_session_at_the_current_version(): void
    {
        $user = User::factory()->active()->create();
        app(CuentaService::class)->block($this->sa, $user->id, 'Revocación de recuerdo de prueba');
        app(CuentaService::class)->unblock($this->sa, $user->id);
        $user->refresh();
        $this->withCookie(Auth::guard()->getRecallerName(), $user->id.'|'.$user->remember_token.'|'.$user->password)
            ->get('/profile')->assertOk()->assertSessionHas(RevocarAccesoService::SESSION_KEY, $user->version_acceso);
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_activation_link_cannot_set_password(): void
    {
        $user = User::factory()->pending()->create();
        $token = Password::broker()->createToken($user);
        $this->travel(61)->minutes();
        $this->postJson('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'Password123', 'password_confirmation' => 'Password123'])
            ->assertUnprocessable();
        $this->assertSame(EstadoCuenta::PENDIENTE, $user->fresh()->estado_cuenta);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_self_block_terminates_the_current_session_and_keeps_another_sa_active(): void
    {
        $other = User::factory()->active()->create()->assignRole('Super Administrador');
        $this->actingAs($this->sa)->postJson($this->accountUrl($this->sa, 'bloquear'), ['motivo' => 'Baja ordinaria solicitada por titular'])->assertRedirect();
        $this->getJson('/profile')->assertUnauthorized();
        $this->assertGuest();
        $this->assertTrue($other->fresh()->cuentaActiva());
    }

    public function test_pending_sa_does_not_protect_the_last_active_sa(): void
    {
        User::factory()->pending()->create()->assignRole('Super Administrador');
        $this->actingAs($this->sa)->postJson($this->accountUrl($this->sa, 'bloquear'), ['motivo' => 'Intento de prueba documentado'])
            ->assertUnprocessable()->assertJsonValidationErrors('ultimo_sa');
    }

    public function test_audit_records_do_not_contain_invitation_tokens_or_password_hashes(): void
    {
        $user = app(CuentaService::class)->create($this->sa, ['name' => 'Persona Invitada', 'email' => 'segura@example.com', 'role' => 'Estudiante']);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $audit = DB::table('bitacora_sistema')->get()->toJson();
        $this->assertStringNotContainsString($token, $audit);
        $this->assertStringNotContainsString($user->password, $audit);
    }
}
