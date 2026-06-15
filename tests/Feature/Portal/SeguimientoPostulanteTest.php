<?php

namespace Tests\Feature\Portal;

use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SeguimientoPostulanteTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdministrator;

    private User $student;

    private Postulante $postulante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->superAdministrator = User::where(
            'email',
            'adriana.choque@avalancha.edu.bo',
        )->firstOrFail();
        $this->student = User::where(
            'email',
            RolesAndUsersSeeder::STUDENT_EMAIL,
        )->firstOrFail();
        $this->postulante = Postulante::create([
            'nombres_post' => 'Valeria Nina',
            'apellidos_post' => 'Choque',
            'email_post' => $this->student->email,
            'gestion_post' => 2026,
            'estado_post' => 'activo',
        ]);
    }

    public function test_super_administrator_can_access_roles_and_permissions(): void
    {
        $this->actingAs($this->superAdministrator)
            ->get(route('admin.sistema.roles-permisos'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sistema/RolesPermisos/Index'));
    }

    public function test_student_cannot_access_roles_and_permissions(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.sistema.roles-permisos'))
            ->assertForbidden();
    }

    public function test_student_can_only_open_their_own_academic_record(): void
    {
        Postulante::create([
            'nombres_post' => 'Otra',
            'apellidos_post' => 'Persona',
            'email_post' => 'otra.persona@postulante.avalancha.edu.bo',
            'gestion_post' => 2026,
            'estado_post' => 'activo',
        ]);

        $this->actingAs($this->student)
            ->get(route('estudiante.ficha'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/MiFicha')
                ->where('postulanteVinculado', true)
                ->where('postulante.id_post', $this->postulante->id_post)
                ->where('postulante.email_post', $this->student->email));
    }

    public function test_student_can_access_privacy_aware_ranking(): void
    {
        $other = Postulante::create([
            'nombres_post' => 'Diego Alejandro',
            'apellidos_post' => 'Mamani Flores',
            'email_post' => 'diego.ranking@postulante.avalancha.edu.bo',
            'gestion_post' => 2026,
            'estado_post' => 'activo',
        ]);
        RendimientoPostulante::create([
            'id_post' => $other->id_post,
            'promedio_general_rend' => 88,
            'nivel_riesgo_rend' => 'Alto rendimiento',
        ]);
        RendimientoPostulante::create([
            'id_post' => $this->postulante->id_post,
            'promedio_general_rend' => 76,
            'nivel_riesgo_rend' => 'Seguimiento regular',
        ]);

        $this->actingAs($this->student)
            ->get(route('estudiante.ranking'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Estudiante/Ranking')
                ->where('postulanteVinculado', true)
                ->has('ranking.data', 2)
                ->where('ranking.data.0.nombre', 'Diego M. F.')
                ->where('ranking.data.1.nombre', 'Valeria Nina Choque')
                ->where('ranking.data.1.es_actual', true));
    }

    public function test_student_remains_blocked_from_administrative_postulantes(): void
    {
        $this->actingAs($this->student)
            ->get(route('postulantes.index'))
            ->assertForbidden();
    }
}
