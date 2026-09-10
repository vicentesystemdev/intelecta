<?php

namespace Database\Seeders;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Models\PersonalInstitucional;
use Illuminate\Database\Seeder;
use LogicException;

class TutoresAcademicosSeeder extends Seeder
{
    public function run(array $personal = []): void
    {
        foreach (['matematica', 'fisica', 'quimica', 'razonamiento', 'paa'] as $key) {
            if (! ($personal[$key] ?? null) instanceof PersonalInstitucional || ! $personal[$key]->exists) {
                throw new LogicException('El fixture alternativo requiere cinco objetos Personal explícitos; no resuelve identidad por texto.');
            }
        }
        if (count(array_unique(array_map(fn ($person) => $person->id_personal, $personal))) !== count($personal)) {
            throw new LogicException('Cada tutor demo requiere un Personal diferente.');
        }
        $tutores = collect([
            [
                'clave' => 'matematica',
                'especialidad_tutor' => 'Matemática',
                'formacion_tutor' => 'Licenciatura en Matemática y formación preuniversitaria',
                'experiencia_tutor' => 'Acompañamiento en álgebra, trigonometría y resolución de problemas para admisión universitaria.',
            ],
            [
                'clave' => 'fisica',
                'especialidad_tutor' => 'Física',
                'formacion_tutor' => 'Ingeniería y docencia en ciencias exactas',
                'experiencia_tutor' => 'Tutoría de mecánica, cinemática y razonamiento físico aplicado a simulacros.',
            ],
            [
                'clave' => 'quimica',
                'especialidad_tutor' => 'Química',
                'formacion_tutor' => 'Licenciatura en Química',
                'experiencia_tutor' => 'Nivelación en química general, estequiometría y lectura cuantitativa de problemas.',
            ],
            [
                'clave' => 'razonamiento',
                'especialidad_tutor' => 'Razonamiento Lógico',
                'formacion_tutor' => 'Psicopedagogía y evaluación de aptitudes',
                'experiencia_tutor' => 'Orientación en razonamiento cuantitativo, interpretación y estrategias de resolución.',
            ],
            [
                'clave' => 'paa',
                'especialidad_tutor' => 'PAA',
                'formacion_tutor' => 'Ingeniería y preparación en pruebas de aptitud académica',
                'experiencia_tutor' => 'Seguimiento de preparación PAA y fortalecimiento de habilidades cuantitativas.',
            ],
        ])->mapWithKeys(function (array $data) use ($personal) {
            $key = $data['clave'];
            unset($data['clave']);

            $tutor = TutorAcademico::updateOrCreate(
                ['personal_id' => $personal[$key]->id_personal],
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
