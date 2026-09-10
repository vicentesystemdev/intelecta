<?php

namespace Database\Seeders;

use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Support\MatrizRbac;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'marco.torrez@avalancha.edu.bo';

    public const STUDENT_EMAIL = 'valeria.nina@postulante.avalancha.edu.bo';

    public const TEACHER_EMAIL = 'rodrigo.salazar@avalancha.edu.bo';

    /** Objects keyed by the explicit applicant fixture index, never resolved from contact email. */
    public array $studentUsers = [];

    /** Explicit scenario keys, passed as objects to Personal fixtures (not resolved by contact). */
    public array $staffUsers = [];

    public function run(): void
    {
        $this->studentUsers = [];
        $this->staffUsers = [];
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = MatrizRbac::CATALOG;

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Administrador',
            'guard_name' => 'web',
        ]);
        $administrador = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);
        $docente = Role::firstOrCreate([
            'name' => 'Docente',
            'guard_name' => 'web',
        ]);
        $estudiante = Role::firstOrCreate([
            'name' => 'Estudiante',
            'guard_name' => 'web',
        ]);

        foreach ([$superAdmin, $administrador, $docente, $estudiante] as $role) {
            $role->syncPermissions(MatrizRbac::forRole($role->name));
        }

        foreach ([
            'ti' => ['Adriana Choque Mamani', 'adriana.choque@avalancha.edu.bo', $superAdmin],
            'coordinacion' => ['Marco Antonio Torrez Quispe', self::ADMIN_EMAIL, $administrador],
            'docente_1' => ['Rodrigo Salazar Condori', self::TEACHER_EMAIL, $docente],
            'docente_2' => ['Carla Mendoza Rojas', 'carla.mendoza@avalancha.edu.bo', $docente],
            'docente_3' => ['Luis Fernando Arce Huanca', 'luis.arce@avalancha.edu.bo', $docente],
            'docente_4' => ['Patricia Vargas Choque', 'patricia.vargas@avalancha.edu.bo', $docente],
        ] as $fixtureKey => [$name, $email, $role]) {
            $this->staffUsers[$fixtureKey] = $this->createUserWithRole($name, $email, $role);
        }

        // These keys explicitly identify applicant fixtures 0, 1 and 2 in the main seeder.
        foreach ([
            0 => ['Valeria Nina Choque', self::STUDENT_EMAIL],
            1 => ['Diego Mamani Flores', 'diego.mamani@postulante.avalancha.edu.bo'],
            2 => ['Mariana Quispe Rojas', 'mariana.quispe@postulante.avalancha.edu.bo'],
        ] as $fixtureIndex => [$name, $email]) {
            $this->studentUsers[$fixtureIndex] = $this->createUserWithRole($name, $email, $estudiante);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createUserWithRole(string $name, string $email, Role $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('Avalancha#2026'),
                'email_verified_at' => now(),
                // Explicit operative DEMO fixture, not the real invitation flow. No notifications.
                'estado_cuenta' => EstadoCuenta::ACTIVA,
            ],
        );

        $user->syncRoles([$role]);

        return $user;
    }
}
