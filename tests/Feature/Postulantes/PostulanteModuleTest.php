<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Support\BirthDate;
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
        $this->administrator = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
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
        $student = User::where('email', RolesAndUsersSeeder::STUDENT_EMAIL)->firstOrFail();

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
                'fecha_nacimiento_post' => BirthDate::today()->subYears(12)->format('d/m/Y'),
            ])
            ->assertSessionHasErrors(['nombres_post', 'ci_post', 'fecha_nacimiento_post']);
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

    public function test_other_catalogs_are_created_as_real_relations_and_reused_by_normalized_name(): void
    {
        $payload = [
            ...$this->validData(),
            'id_col' => null,
            'crear_otro_colegio' => true,
            'otro_colegio_nombre' => '  Colegio   Nueva Esperanza  ',
            'id_uni' => null,
            'id_car' => null,
            'crear_otra_universidad' => true,
            'otra_universidad_nombre' => 'Universidad Técnica del Altiplano',
            'otra_universidad_sigla' => 'UTA',
            'crear_otra_carrera' => true,
            'otra_carrera_nombre' => 'Ingeniería Mecatrónica',
        ];

        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), $payload)->assertRedirect();
        $postulante = Postulante::where('ci_post', '8765432')->firstOrFail();
        $this->assertSame('Colegio Nueva Esperanza', $postulante->colegio->nombre_col);
        $this->assertSame('Universidad Técnica del Altiplano', $postulante->carrera->universidad->nombre_uni);
        $this->assertSame('Ingeniería Mecatrónica', $postulante->carrera->nombre_car);

        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), [
            ...$this->validData(),
            'ci_post' => '8765433',
            'email_post' => 'otra@example.test',
            'id_col' => null,
            'crear_otro_colegio' => true,
            'otro_colegio_nombre' => 'colegio nueva esperanza',
        ])->assertRedirect();
        $this->assertSame(1, Colegio::query()->whereRaw("LOWER(nombre_col) = 'colegio nueva esperanza'")->count());
    }

    public function test_other_catalog_validation_is_atomic_and_rejects_cross_university_career(): void
    {
        $otraUniversidad = Universidad::create(['nombre_uni' => 'Universidad B', 'sigla_uni' => 'UB', 'estado_uni' => 'activo']);
        $otraCarrera = Carrera::create(['id_uni' => $otraUniversidad->id_uni, 'nombre_car' => 'Ingeniería Civil', 'estado_car' => 'activo']);

        $universidadesAntes = Universidad::count();
        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), [
            ...$this->validData(),
            'id_uni' => null,
            'id_car' => null,
            'crear_otra_universidad' => true,
            'otra_universidad_nombre' => 'Universidad sin carrera',
        ])->assertUnprocessable()->assertJsonValidationErrors('id_car');
        $this->assertSame($universidadesAntes, Universidad::count());

        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), [
            ...$this->validData(),
            'ci_post' => '8765433',
            'email_post' => 'nueva.carrera@example.test',
            'id_car' => null,
            'crear_otra_carrera' => true,
            'otra_carrera_nombre' => 'Ingeniería Ambiental',
        ])->assertRedirect();
        $postulanteNuevaCarrera = Postulante::where('ci_post', '8765433')->firstOrFail();
        $this->assertSame($this->universidad->id_uni, $postulanteNuevaCarrera->carrera->id_uni);
        $this->assertSame('Ingeniería Ambiental', $postulanteNuevaCarrera->carrera->nombre_car);

        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), [
            ...$this->validData(),
            'id_car' => $otraCarrera->id_car,
        ])->assertUnprocessable()->assertJsonValidationErrors('id_car');

        Postulante::create($this->validData());
        $before = [Colegio::count(), Universidad::count(), Carrera::count()];
        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), [
            ...$this->validData(),
            'crear_otro_colegio' => true,
            'id_col' => null,
            'otro_colegio_nombre' => 'Colegio que no debe persistir',
        ])->assertUnprocessable()->assertJsonValidationErrors('ci_post');
        $this->assertSame($before, [Colegio::count(), Universidad::count(), Carrera::count()]);
    }

    /**
     * @return array<string, mixed>
     */
    public function test_ordinary_academic_crud_cannot_assign_or_replace_user_id(): void
    {
        $user = User::role('Estudiante')->firstOrFail();
        $this->actingAs($this->administrator)->postJson(route('postulantes.store'), [...$this->validData(), 'user_id' => $user->id])
            ->assertRedirect();
        $postulante = Postulante::where('ci_post', '8765432')->firstOrFail();
        $this->assertNull($postulante->user_id);
        $postulante->user()->associate($user)->save();
        $this->putJson(route('postulantes.update', $postulante), [...$this->validData(), 'user_id' => null, 'email_post' => 'contacto.modificado@example.com'])
            ->assertRedirect();
        $this->assertSame($user->id, $postulante->fresh()->user_id);
        $this->assertSame('contacto.modificado@example.com', $postulante->fresh()->email_post);
    }

    private function validData(): array
    {
        return [
            'nombres_post' => 'Lucía',
            'apellidos_post' => 'Fernández Rojas',
            'ci_post' => '8765432',
            'email_post' => 'lucia.fernandez@correo.test',
            'celular_post' => '70123456',
            'fecha_nacimiento_post' => BirthDate::today()->subYears(18)->toDateString(),
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
