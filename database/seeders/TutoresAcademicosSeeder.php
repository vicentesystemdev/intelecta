<?php

namespace Database\Seeders;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use Illuminate\Database\Seeder;

class TutoresAcademicosSeeder extends Seeder
{
    public function run(): void
    {
        $tutores = collect([
            [
                'clave' => 'matematica',
                'nombres_tutor' => 'Daniela',
                'apellidos_tutor' => 'Vargas Flores',
                'ci_tutor' => 'TUT-1001',
                'celular_tutor' => '72001001',
                'correo_tutor' => 'daniela.vargas@avalancha.edu.bo',
                'especialidad_tutor' => 'Matemática',
                'formacion_tutor' => 'Licenciatura en Matemática y formación preuniversitaria',
                'experiencia_tutor' => 'Acompañamiento en álgebra, trigonometría y resolución de problemas para admisión universitaria.',
            ],
            [
                'clave' => 'fisica',
                'nombres_tutor' => 'Marcelo',
                'apellidos_tutor' => 'Quispe Mamani',
                'ci_tutor' => 'TUT-1002',
                'celular_tutor' => '72001002',
                'correo_tutor' => 'marcelo.quispe@avalancha.edu.bo',
                'especialidad_tutor' => 'Física',
                'formacion_tutor' => 'Ingeniería y docencia en ciencias exactas',
                'experiencia_tutor' => 'Tutoría de mecánica, cinemática y razonamiento físico aplicado a simulacros.',
            ],
            [
                'clave' => 'quimica',
                'nombres_tutor' => 'Paola',
                'apellidos_tutor' => 'Rojas Choque',
                'ci_tutor' => 'TUT-1003',
                'celular_tutor' => '72001003',
                'correo_tutor' => 'paola.rojas@avalancha.edu.bo',
                'especialidad_tutor' => 'Química',
                'formacion_tutor' => 'Licenciatura en Química',
                'experiencia_tutor' => 'Nivelación en química general, estequiometría y lectura cuantitativa de problemas.',
            ],
            [
                'clave' => 'razonamiento',
                'nombres_tutor' => 'Álvaro',
                'apellidos_tutor' => 'Mendoza Apaza',
                'ci_tutor' => 'TUT-1004',
                'celular_tutor' => '72001004',
                'correo_tutor' => 'alvaro.mendoza@avalancha.edu.bo',
                'especialidad_tutor' => 'Razonamiento Lógico',
                'formacion_tutor' => 'Psicopedagogía y evaluación de aptitudes',
                'experiencia_tutor' => 'Orientación en razonamiento cuantitativo, interpretación y estrategias de resolución.',
            ],
            [
                'clave' => 'paa',
                'nombres_tutor' => 'Natalia',
                'apellidos_tutor' => 'Condori Lima',
                'ci_tutor' => 'TUT-1005',
                'celular_tutor' => '72001005',
                'correo_tutor' => 'natalia.condori@avalancha.edu.bo',
                'especialidad_tutor' => 'PAA',
                'formacion_tutor' => 'Ingeniería y preparación en pruebas de aptitud académica',
                'experiencia_tutor' => 'Seguimiento de preparación PAA y fortalecimiento de habilidades cuantitativas.',
            ],
        ])->mapWithKeys(function (array $data) {
            $key = $data['clave'];
            unset($data['clave']);

            $tutor = TutorAcademico::updateOrCreate(
                ['ci_tutor' => $data['ci_tutor']],
                [
                    ...$data,
                    'estado_tutor' => 'activo',
                    'observacion_tutor' => 'Perfil disponible para asignación institucional.',
                ],
            );

            return [$key => $tutor];
        });

        $grupos = GrupoAcademico::query()
            ->whereIn('codigo_grupo', [
                'UMSA-MAT-A',
                'UMSA-FIS-B',
                'EMI-INT-A',
                'PAA-UCB-A',
                'UPEA-SIS-A',
            ])
            ->get()
            ->keyBy('codigo_grupo');

        $asignacionesGrupo = [
            ['grupo' => 'UMSA-MAT-A', 'tutor' => 'matematica', 'materia' => 'Matemática'],
            ['grupo' => 'UMSA-FIS-B', 'tutor' => 'fisica', 'materia' => 'Física'],
            ['grupo' => 'EMI-INT-A', 'tutor' => 'quimica', 'materia' => 'Química'],
            ['grupo' => 'PAA-UCB-A', 'tutor' => 'paa', 'materia' => 'PAA'],
            ['grupo' => 'UPEA-SIS-A', 'tutor' => 'razonamiento', 'materia' => 'Razonamiento Lógico'],
        ];

        foreach ($asignacionesGrupo as $item) {
            $grupo = $grupos->get($item['grupo']);
            $tutor = $tutores->get($item['tutor']);

            if (! $grupo || ! $tutor) {
                continue;
            }

            AsignacionTutor::updateOrCreate(
                [
                    'id_tutor' => $tutor->id_tutor,
                    'id_grupo' => $grupo->id_grupo,
                ],
                [
                    'id_prog' => $grupo->id_prog,
                    'materia_referencia_asig' => $item['materia'],
                    'rol_asig' => 'Tutor responsable',
                    'fecha_inicio_asig' => '2026-06-15',
                    'fecha_fin_asig' => null,
                    'estado_asig' => 'activo',
                    'observacion_asig' => 'Asignación tutorial para seguimiento académico del grupo.',
                ],
            );
        }

        $programa = ProgramaAcademico::query()
            ->where('codigo_prog', 'REF-STEM-2026')
            ->first();

        if ($programa && $tutores->has('matematica')) {
            AsignacionTutor::updateOrCreate(
                [
                    'id_tutor' => $tutores['matematica']->id_tutor,
                    'id_prog' => $programa->id_prog,
                    'id_grupo' => null,
                ],
                [
                    'materia_referencia_asig' => 'Ciencias exactas',
                    'rol_asig' => 'Tutor de programa',
                    'fecha_inicio_asig' => '2026-06-15',
                    'fecha_fin_asig' => null,
                    'estado_asig' => 'activo',
                    'observacion_asig' => 'Cobertura tutorial transversal para el programa académico.',
                ],
            );
        }
    }
}
