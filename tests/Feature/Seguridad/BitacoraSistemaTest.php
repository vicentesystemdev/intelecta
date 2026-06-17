<?php

namespace Tests\Feature\Seguridad;

use App\Domains\Seguridad\Models\BitacoraSistema;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BitacoraSistemaTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private User $superAdministrator;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->administrator = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $this->superAdministrator = User::where('email', 'adriana.choque@avalancha.edu.bo')->firstOrFail();
        $this->student = User::where('email', RolesAndUsersSeeder::STUDENT_EMAIL)->firstOrFail();
    }

    public function test_administrator_can_access_bitacora(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('admin.sistema.bitacora.index'))
            ->assertOk();
    }

    public function test_super_administrator_can_access_bitacora(): void
    {
        $this->actingAs($this->superAdministrator)
            ->get(route('admin.sistema.bitacora.index'))
            ->assertOk();
    }

    public function test_student_cannot_access_bitacora(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.sistema.bitacora.index'))
            ->assertForbidden();
    }

    public function test_registering_basic_event_creates_bitacora_row(): void
    {
        $this->actingAs($this->administrator);

        app(BitacoraService::class)->registrar([
            'accion' => 'evento_prueba',
            'modulo' => 'Pruebas',
            'entidad' => 'tests',
            'entidad_id' => '1',
            'descripcion' => 'Evento institucional de prueba.',
            'valores_nuevos' => ['password' => 'no-debe-guardarse'],
        ]);

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'evento_prueba',
            'modulo' => 'Pruebas',
        ]);

        $evento = BitacoraSistema::where('accion', 'evento_prueba')->firstOrFail();
        $this->assertSame('[dato protegido]', $evento->valores_nuevos['password']);
    }

    public function test_bitacora_export_requires_export_permission(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('admin.sistema.bitacora.exportar'))
            ->assertOk();

        $this->actingAs($this->student)
            ->get(route('admin.sistema.bitacora.exportar'))
            ->assertForbidden();
    }

    public function test_successful_login_creates_bitacora_event(): void
    {
        $this->post('/login', [
            'email' => RolesAndUsersSeeder::ADMIN_EMAIL,
            'password' => 'Avalancha#2026',
        ])->assertRedirect();

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'login_exitoso',
            'correo_usuario' => RolesAndUsersSeeder::ADMIN_EMAIL,
            'severidad' => 'seguridad',
        ]);
    }

    public function test_logout_creates_bitacora_event(): void
    {
        $this->actingAs($this->administrator)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'logout',
            'correo_usuario' => RolesAndUsersSeeder::ADMIN_EMAIL,
        ]);
    }

    public function test_failed_login_creates_event_without_password(): void
    {
        $this->post('/login', [
            'email' => RolesAndUsersSeeder::ADMIN_EMAIL,
            'password' => 'credencial-incorrecta',
        ])->assertSessionHasErrors('email');

        $evento = BitacoraSistema::where('accion', 'login_fallido')->latest('id_bitacora')->firstOrFail();

        $this->assertSame(
            RolesAndUsersSeeder::ADMIN_EMAIL,
            $evento->valores_nuevos['correo_intentado'],
        );
        $this->assertArrayNotHasKey('password', $evento->valores_nuevos);
        $this->assertStringNotContainsString(
            'credencial-incorrecta',
            json_encode($evento->valores_nuevos, JSON_THROW_ON_ERROR),
        );
    }

    public function test_student_login_and_logout_are_registered(): void
    {
        $this->post('/login', [
            'email' => RolesAndUsersSeeder::STUDENT_EMAIL,
            'password' => 'Avalancha#2026',
        ])->assertRedirect();

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'login_exitoso',
            'correo_usuario' => RolesAndUsersSeeder::STUDENT_EMAIL,
            'rol_usuario' => 'Estudiante',
        ]);

        $this->post('/logout')->assertRedirect('/');

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'logout',
            'correo_usuario' => RolesAndUsersSeeder::STUDENT_EMAIL,
        ]);
    }

    public function test_repeated_failed_logins_create_lockout_event(): void
    {
        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->post('/login', [
                'email' => 'cuenta.bloqueada@avalancha.edu.bo',
                'password' => 'credencial-incorrecta',
            ]);
        }

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'login_bloqueado',
            'entidad' => 'autenticacion',
            'entidad_id' => 'cuenta.bloqueada@avalancha.edu.bo',
            'severidad' => 'seguridad',
        ]);
    }

    public function test_duplicate_login_events_are_ignored_within_five_seconds(): void
    {
        event(new Login('web', $this->administrator, false));
        event(new Login('web', $this->administrator, false));

        $this->assertSame(
            1,
            BitacoraSistema::query()
                ->where('accion', 'login_exitoso')
                ->where('user_id', $this->administrator->id)
                ->count(),
        );
    }

    public function test_duplicate_logout_events_are_ignored_within_five_seconds(): void
    {
        event(new Logout('web', $this->administrator));
        event(new Logout('web', $this->administrator));

        $this->assertSame(
            1,
            BitacoraSistema::query()
                ->where('accion', 'logout')
                ->where('user_id', $this->administrator->id)
                ->count(),
        );
    }

    public function test_non_authentication_actions_are_not_deduplicated(): void
    {
        $this->actingAs($this->administrator);
        $service = app(BitacoraService::class);
        $event = [
            'accion' => 'exportar_pdf',
            'modulo' => 'Reportes Académicos',
            'entidad' => 'reporte',
            'entidad_id' => 'rendimiento',
            'descripcion' => 'Se descargó un reporte académico.',
        ];

        $service->registrar($event);
        $service->registrar($event);

        $this->assertSame(
            2,
            BitacoraSistema::query()
                ->where('accion', 'exportar_pdf')
                ->where('user_id', $this->administrator->id)
                ->count(),
        );
    }

    public function test_deduplication_does_not_merge_different_users(): void
    {
        event(new Login('web', $this->administrator, false));
        event(new Login('web', $this->student, false));

        $this->assertSame(
            2,
            BitacoraSistema::query()
                ->where('accion', 'login_exitoso')
                ->whereIn('user_id', [$this->administrator->id, $this->student->id])
                ->count(),
        );
    }
}
