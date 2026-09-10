<?php

namespace Tests\Feature\Institucional;

use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Support\PermisosOrganizacion;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Database\Seeders\CargosSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PersonalInstitucionalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrganizacionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_fixture_keeps_all_three_blocks_and_tutor_schema(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseCount('cargos', 8);
        $this->assertDatabaseCount('personal_institucional', 5);
        $this->assertDatabaseCount('tutores_academicos', 4);
        $this->assertSame(80, Permission::count());
        $admin = User::role('Administrador')->sole();
        $this->assertSame('Coordinador Académico', $admin->personalInstitucional->cargo->nombre_cargo);
        $this->assertSame('Marco Antonio', $admin->personalInstitucional->nombres);
        $this->assertNull(User::role('Super Administrador')->sole()->personalInstitucional);
        foreach (User::role('Docente')->get() as $user) {
            $this->assertSame('Docente', $user->personalInstitucional->cargo->nombre_cargo);
            $this->assertTrue(TutorAcademico::whereHas('personal', fn ($query) => $query->where('user_id', $user->id))->exists());
            foreach (PermisosOrganizacion::ALL as $permission) {
                $this->assertFalse($user->can($permission));
            }
        }
        foreach (User::role('Estudiante')->get() as $user) {
            $this->assertNull($user->personalInstitucional);
            $this->assertNotNull($user->postulante);
        }
        $this->assertTrue(Schema::hasColumn('tutores_academicos', 'personal_id'));
        $this->assertFalse(Schema::hasColumn('tutores_academicos', 'user_id'));
        $this->assertSame(9, User::where('estado_cuenta', 'activa')->whereNotNull('email_verified_at')->count());
        $this->assertSame(3, Postulante::whereNotNull('user_id')->count());
        $this->assertSame(69, Postulante::whereNull('user_id')->count());
        Notification::assertNothingSent();
    }

    public function test_personal_fixture_uses_passed_objects_not_roles_names_emails_or_fixed_ids(): void
    {
        User::factory()->count(3)->create();
        $users = [];
        foreach (['coordinacion', 'docente_1', 'docente_2', 'docente_3', 'docente_4'] as $key) {
            $users[$key] = User::factory()->pending()->create();
        }
        $positions = app(CargosSeeder::class);
        $positions->run();
        $seed = app(PersonalInstitucionalSeeder::class);
        $seed->run($users, $positions->cargos);
        $seed->run($users, $positions->cargos);
        $this->assertDatabaseCount('personal_institucional', 5);
        foreach ($users as $key => $user) {
            $profile = $user->fresh()->personalInstitucional;
            $this->assertSame($user->id, $profile->user_id);
            $this->assertSame($positions->cargos[$key === 'coordinacion' ? 'coordinacion' : 'docencia']->id_cargo, $profile->cargo_id);
            $this->assertSame([], $user->fresh()->getRoleNames()->all());
            $this->assertSame('pendiente', $user->fresh()->estado_cuenta->value);
        }
    }

    public function test_factories_are_explicit_and_do_not_create_accounts_implicitly(): void
    {
        $this->assertSame('activo', Cargo::factory()->active()->create()->estado->value);
        $this->assertSame('inactivo', Cargo::factory()->inactive()->create()->estado->value);
        foreach (['pending' => 'pendiente', 'active' => 'activo', 'inactive' => 'inactivo'] as $factory => $state) {
            $person = PersonalInstitucional::factory()->$factory()->create();
            $this->assertSame($state, $person->estado->value);
            $this->assertNull($person->user_id);
            $this->assertNull($person->cargo_id);
        }
        $this->assertDatabaseCount('users', 0);
    }
}
