<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostulanteModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private Colegio $colegio;

    private Carrera $carrera;

    private Universidad $universidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->administrator = User::where('email', 'admin@intelecta.test')->firstOrFail();
        $this->colegio = Colegio::create([
            'nombre_col' => 'Colegio Nacional San Simón',
            'tipo_col' => 'Público',
            'estado_col' => 'activo',
        ]);
        $this->universidad = Universidad::create([
            'nombre_uni' => 'Universidad Mayor de San Andrés',
            'sigla_uni' => 'UMSA',
            'tipo_uni' => 'Pública',
            'departamento_uni' => 'La Paz',
            'nivel_exigencia_matematica_uni' => 'Alta',
            'estado_uni' => 'activo',
        ]);
        $this->carrera = Carrera::create([
            'id_uni' => $this->universidad->id_uni,
            'nombre_car' => 'Ingeniería de Sistemas',
            'area_car' => 'Ingeniería',
            'nivel_exigencia_matematica_car' => 'Alta',
            'estado_car' => 'activo',
        ]);
    }

    public function test_administrator_can_view_the_postulantes_index(): void
    {
        Postulante::create($this->validData());

        $this->actingAs($this->administrator)
            ->get(route('postulantes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Postulantes/Index')
                ->has('postulantes.data', 1)
                ->where('postulantes.data.0.ci_post', '8765432')
                ->where('postulantes.data.0.colegio.nombre_col', 'Colegio Nacional San Simón')
                ->where('postulantes.data.0.carrera.nombre_car', 'Ingeniería de Sistemas')
                ->where('postulantes.data.0.carrera.universidad.sigla_uni', 'UMSA')
            );
    }

    public function test_user_without_permission_cannot_view_postulantes(): void
    {
        $student = User::where('email', 'estudiante@intelecta.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('postulantes.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_create_a_postulante(): void
    {
        $response = $this->actingAs($this->administrator)
            ->post(route('postulantes.store'), $this->validData());

        $postulante = Postulante::where('ci_post', '8765432')->firstOrFail();

        $response
            ->assertRedirect(route('postulantes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('postulantes', [
            'nombres_post' => 'Lucía',
            'apellidos_post' => 'Fernández Rojas',
            'id_col' => $this->colegio->id_col,
            'id_car' => $this->carrera->id_car,
            'estado_post' => 'activo',
        ]);
    }

    public function test_postulante_validation_rejects_invalid_data_and_duplicate_ci(): void
    {
        Postulante::create($this->validData());

        $this->actingAs($this->administrator)
            ->post(route('postulantes.store'), [
                ...$this->validData(),
                'nombres_post' => '',
                'edad_post' => 12,
            ])
            ->assertSessionHasErrors(['nombres_post', 'ci_post', 'edad_post']);
    }

    public function test_administrator_can_update_a_postulante(): void
    {
        $postulante = Postulante::create($this->validData());
        $otraCarrera = Carrera::create([
            'id_uni' => $this->universidad->id_uni,
            'nombre_car' => 'Ingeniería Industrial',
            'area_car' => 'Ingeniería',
            'nivel_exigencia_matematica_car' => 'Alta',
            'estado_car' => 'activo',
        ]);

        $this->actingAs($this->administrator)
            ->put(route('postulantes.update', $postulante), [
                ...$this->validData(),
                'id_car' => $otraCarrera->id_car,
            ])
            ->assertRedirect(route('postulantes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('postulantes', [
            'id_post' => $postulante->id_post,
            'id_car' => $otraCarrera->id_car,
        ]);
    }

    public function test_postulante_career_belongs_to_a_university(): void
    {
        $postulante = Postulante::create($this->validData());

        $this->assertTrue($postulante->carrera->is($this->carrera));
        $this->assertTrue($postulante->carrera->universidad->is($this->universidad));
        $this->assertTrue($postulante->universidad->is($this->universidad));
        $this->assertTrue($this->universidad->carreras->contains($this->carrera));
    }

    public function test_administrator_can_filter_postulantes_by_university(): void
    {
        Postulante::create($this->validData());
        $otraUniversidad = Universidad::create([
            'nombre_uni' => 'Escuela Militar de Ingeniería',
            'sigla_uni' => 'EMI',
            'tipo_uni' => 'Privada',
            'estado_uni' => 'activo',
        ]);
        $otraCarrera = Carrera::create([
            'id_uni' => $otraUniversidad->id_uni,
            'nombre_car' => 'Ingeniería de Sistemas',
            'area_car' => 'Ingeniería',
            'nivel_exigencia_matematica_car' => 'Alta',
            'estado_car' => 'activo',
        ]);
        Postulante::create([
            ...$this->validData(),
            'nombres_post' => 'Mateo',
            'apellidos_post' => 'Salinas',
            'ci_post' => '9988776',
            'id_car' => $otraCarrera->id_car,
        ]);

        $this->actingAs($this->administrator)
            ->get(route('postulantes.index', ['id_uni' => $this->universidad->id_uni]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('postulantes.data', 1)
                ->where('postulantes.data.0.nombres_post', 'Lucía')
                ->where('postulantes.data.0.carrera.universidad.sigla_uni', 'UMSA')
            );
    }

    public function test_administrator_can_change_postulante_status(): void
    {
        $postulante = Postulante::create($this->validData());

        $this->actingAs($this->administrator)
            ->patch(route('postulantes.cambiar-estado', $postulante))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('postulantes', [
            'id_post' => $postulante->id_post,
            'estado_post' => 'inactivo',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'nombres_post' => 'Lucía',
            'apellidos_post' => 'Fernández Rojas',
            'ci_post' => '8765432',
            'email_post' => 'lucia.fernandez@correo.test',
            'celular_post' => '70123456',
            'edad_post' => 18,
            'id_col' => $this->colegio->id_col,
            'id_uni' => $this->universidad->id_uni,
            'id_car' => $this->carrera->id_car,
            'turno_post' => 'Mañana',
            'gestion_post' => 2026,
            'estado_post' => 'activo',
            'observaciones_post' => 'Seguimiento académico inicial.',
        ];
    }
}
