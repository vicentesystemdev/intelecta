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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.ver',

            'programas.ver',
            'programas.crear',
            'programas.editar',
            'grupos.ver',
            'grupos.crear',
            'grupos.editar',
            'tutores.ver',
            'tutores.crear',
            'tutores.editar',
            'asignaciones-tutores.ver',
            'asignaciones-tutores.crear',
            'asignaciones-tutores.editar',
            'inscripciones.ver',
            'inscripciones.crear',
            'inscripciones.editar',
            'matriculas-cuotas.ver',
            'matriculas-cuotas.crear',
            'matriculas-cuotas.editar',
            'habilitacion-academica.ver',
            'habilitacion-academica.editar',
            'asistencia.ver',
            'asistencia.crear',
            'asistencia.editar',
            'simulacros.ver',
            'simulacros.crear',
            'simulacros.editar',

            'postulantes.ver',
            'postulantes.crear',
            'postulantes.editar',
            'postulantes.eliminar',
            'ficha-academica.ver',
            'ranking.ver',

            'materias.ver',
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
            'resultados.ver',

            'reportes.ver',
            'indicadores.ver',
            'learning_analytics.ver',

            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'configuracion.ver',

            // Permisos heredados que siguen vinculados a rutas y roles existentes.
            'docentes.ver',
            'docentes.crear',
            'docentes.editar',
            'docentes.eliminar',
            'evaluaciones.ver',
            'evaluaciones.crear',
            'evaluaciones.editar',
            'evaluaciones.cerrar',
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
        $administrador->syncPermissions($permissions);

        $docente->syncPermissions([
            'dashboard.ver',
            'programas.ver',
            'grupos.ver',
            'postulantes.ver',
            'ficha-academica.ver',
            'ranking.ver',
            'asistencia.ver',
            'simulacros.ver',
            'materias.ver',
            'areas.ver',
            'temas.ver',
            'preguntas.ver',
            'preguntas.crear',
            'plantillas.ver',
            'resultados.ver',
            'reportes.ver',
            'indicadores.ver',
            'learning_analytics.ver',
            'evaluaciones.ver',
        ]);

        $estudiante->syncPermissions([]);

        foreach ([
            ['Adriana Choque Mamani', 'adriana.choque@avalancha.edu.bo', $superAdmin],
            ['Marco Antonio Torrez Quispe', 'marco.torrez@avalancha.edu.bo', $administrador],
            ['Rodrigo Salazar Condori', 'rodrigo.salazar@avalancha.edu.bo', $docente],
            ['Carla Mendoza Rojas', 'carla.mendoza@avalancha.edu.bo', $docente],
            ['Luis Fernando Arce Huanca', 'luis.arce@avalancha.edu.bo', $docente],
            ['Patricia Vargas Choque', 'patricia.vargas@avalancha.edu.bo', $docente],
            ['Valeria Nina Choque', 'valeria.nina@postulante.avalancha.edu.bo', $estudiante],
            ['Diego Mamani Flores', 'diego.mamani@postulante.avalancha.edu.bo', $estudiante],
            ['Mariana Quispe Rojas', 'mariana.quispe@postulante.avalancha.edu.bo', $estudiante],
        ] as [$name, $email, $role]) {
            $this->createUserWithRole($name, $email, $role);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createUserWithRole(string $name, string $email, Role $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('Avalancha#2026'),
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role]);
    }
}
