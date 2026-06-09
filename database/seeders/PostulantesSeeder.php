<?php

namespace Database\Seeders;

use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Seeder;

class PostulantesSeeder extends Seeder
{
    public function run(): void
    {
        $colegios = Colegio::pluck('id_col', 'nombre_col');
        $carreras = Carrera::query()
            ->with('universidad:id_uni,sigla_uni')
            ->get()
            ->keyBy(fn (Carrera $carrera) => "{$carrera->universidad->sigla_uni}|{$carrera->nombre_car}");

        $postulantes = [
            ['nombres_post' => 'María Fernanda', 'apellidos_post' => 'López Vargas', 'ci_post' => '8945121', 'email_post' => 'maria.lopez@correo.test', 'celular_post' => '72014521', 'edad_post' => 18, 'colegio' => 'Colegio Nacional San Simón', 'universidad' => 'UMSA', 'carrera' => 'Ingeniería de Sistemas', 'turno_post' => 'Mañana', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Carlos Andrés', 'apellidos_post' => 'Rojas Méndez', 'ci_post' => '7632198', 'email_post' => 'carlos.rojas@correo.test', 'celular_post' => '76403218', 'edad_post' => 19, 'colegio' => 'Unidad Educativa Bolívar', 'universidad' => 'EMI', 'carrera' => 'Ingeniería Comercial', 'turno_post' => 'Tarde', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Valentina', 'apellidos_post' => 'Cruz Salazar', 'ci_post' => '9256134', 'email_post' => 'valentina.cruz@correo.test', 'celular_post' => '70783120', 'edad_post' => 17, 'colegio' => 'Colegio Alemán Santa María', 'universidad' => 'UMSA', 'carrera' => 'Psicología', 'turno_post' => 'Mañana', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'José Luis', 'apellidos_post' => 'Mamani Quispe', 'ci_post' => '6812457', 'email_post' => 'jose.mamani@correo.test', 'celular_post' => '67541209', 'edad_post' => 20, 'colegio' => 'Unidad Educativa Técnico Humanístico', 'universidad' => 'UMSA', 'carrera' => 'Ingeniería Civil', 'turno_post' => 'Noche', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Andrea Sofía', 'apellidos_post' => 'Flores Rocha', 'ci_post' => '9124875', 'email_post' => 'andrea.flores@correo.test', 'celular_post' => '72241865', 'edad_post' => 18, 'colegio' => 'Colegio Nacional San Simón', 'universidad' => 'UNIFRANZ', 'carrera' => 'Ingeniería de Sistemas', 'turno_post' => 'Tarde', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Diego Alejandro', 'apellidos_post' => 'Paredes Soto', 'ci_post' => '8451726', 'email_post' => 'diego.paredes@correo.test', 'celular_post' => '75963124', 'edad_post' => 19, 'colegio' => 'Instituto Americano', 'universidad' => 'EMI', 'carrera' => 'Ingeniería de Sistemas', 'turno_post' => 'Mañana', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Camila', 'apellidos_post' => 'Gutiérrez Arce', 'ci_post' => '9362184', 'email_post' => 'camila.gutierrez@correo.test', 'celular_post' => '70321846', 'edad_post' => 17, 'colegio' => 'Unidad Educativa Bolívar', 'universidad' => 'UCB', 'carrera' => 'Economía', 'turno_post' => 'Tarde', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Luis Fernando', 'apellidos_post' => 'Vega Torrico', 'ci_post' => '7145289', 'email_post' => 'luis.vega@correo.test', 'celular_post' => '69451728', 'edad_post' => 21, 'colegio' => 'Colegio Don Bosco', 'universidad' => 'UMSA', 'carrera' => 'Derecho', 'turno_post' => 'Noche', 'gestion_post' => 2026, 'estado_post' => 'inactivo'],
            ['nombres_post' => 'Natalia Belén', 'apellidos_post' => 'Rivera Campos', 'ci_post' => '9581243', 'email_post' => 'natalia.rivera@correo.test', 'celular_post' => '71894523', 'edad_post' => 18, 'colegio' => 'Colegio Alemán Santa María', 'universidad' => 'UPEA', 'carrera' => 'Ciencias de la Educación', 'turno_post' => 'Mañana', 'gestion_post' => 2026, 'estado_post' => 'activo'],
            ['nombres_post' => 'Miguel Ángel', 'apellidos_post' => 'Sánchez Lima', 'ci_post' => '7926415', 'email_post' => 'miguel.sanchez@correo.test', 'celular_post' => '75261489', 'edad_post' => 19, 'colegio' => 'Colegio Don Bosco', 'universidad' => 'UMSA', 'carrera' => 'Ingeniería de Sistemas', 'turno_post' => 'Tarde', 'gestion_post' => 2026, 'estado_post' => 'activo'],
        ];

        foreach ($postulantes as $postulante) {
            $colegio = $postulante['colegio'];
            $carreraKey = "{$postulante['universidad']}|{$postulante['carrera']}";
            unset($postulante['colegio'], $postulante['universidad'], $postulante['carrera']);

            Postulante::updateOrCreate(
                ['ci_post' => $postulante['ci_post']],
                [
                    ...$postulante,
                    'id_col' => $colegios[$colegio],
                    'id_car' => $carreras[$carreraKey]->id_car,
                ]
            );
        }
    }
}
