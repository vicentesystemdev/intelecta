<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.ver',

            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            'roles.ver',
            'roles.crear',
            'roles.editar',

            'postulantes.ver',
            'postulantes.crear',
            'postulantes.editar',
            'postulantes.eliminar',

            'docentes.ver',
            'docentes.crear',
            'docentes.editar',
            'docentes.eliminar',

            'evaluaciones.ver',
            'evaluaciones.crear',
            'evaluaciones.editar',
            'evaluaciones.cerrar',

            'areas.ver',
            'areas.crear',
            'areas.editar',

            'temas.ver',
            'temas.crear',
            'temas.editar',

            'preguntas.ver',
            'preguntas.crear',
            'preguntas.editar',
            'preguntas.eliminar',

            'plantillas.ver',
            'plantillas.crear',
            'plantillas.editar',
            'plantillas.eliminar',

            'reportes.ver',
            'learning_analytics.ver',
        ];

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

        $superAdmin->syncPermissions($permissions);

        $administrador->syncPermissions([
            'dashboard.ver',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'postulantes.ver',
            'postulantes.crear',
            'postulantes.editar',
            'postulantes.eliminar',
            'docentes.ver',
            'docentes.crear',
            'docentes.editar',
            'docentes.eliminar',
            'evaluaciones.ver',
            'evaluaciones.crear',
            'evaluaciones.editar',
            'evaluaciones.cerrar',
            'areas.ver',
            'areas.crear',
            'areas.editar',
            'temas.ver',
            'temas.crear',
            'temas.editar',
            'preguntas.ver',
            'preguntas.crear',
            'preguntas.editar',
            'preguntas.eliminar',
            'plantillas.ver',
            'plantillas.crear',
            'plantillas.editar',
            'plantillas.eliminar',
            'reportes.ver',
            'learning_analytics.ver',
        ]);

        $docente->syncPermissions([
            'dashboard.ver',
            'evaluaciones.ver',
            'evaluaciones.crear',
            'evaluaciones.editar',
            'evaluaciones.cerrar',
            'areas.ver',
            'areas.crear',
            'areas.editar',
            'temas.ver',
            'temas.crear',
            'temas.editar',
            'preguntas.ver',
            'preguntas.crear',
            'preguntas.editar',
            'plantillas.ver',
            'plantillas.crear',
            'plantillas.editar',
            'reportes.ver',
            'learning_analytics.ver',
        ]);

        $estudiante->syncPermissions([
            'dashboard.ver',
        ]);

        $this->createUserWithRole(
            name: 'Super Administrador',
            email: 'superadmin@intelecta.test',
            role: $superAdmin
        );

        $this->createUserWithRole(
            name: 'Administrador Académico',
            email: 'admin@intelecta.test',
            role: $administrador
        );

        $this->createUserWithRole(
            name: 'Docente Académico',
            email: 'docente@intelecta.test',
            role: $docente
        );

        $this->createUserWithRole(
            name: 'Estudiante Institucional',
            email: 'estudiante@intelecta.test',
            role: $estudiante
        );
    }

    private function createUserWithRole(string $name, string $email, Role $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role]);
    }
}
