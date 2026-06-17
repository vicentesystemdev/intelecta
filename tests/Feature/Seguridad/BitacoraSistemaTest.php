<?php

namespace Tests\Feature\Seguridad;

use App\Domains\Seguridad\Models\BitacoraSistema;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
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
}
