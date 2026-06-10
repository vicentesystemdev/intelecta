<?php

namespace Database\Seeders;

use App\Domains\Academico\Enums\NivelSeguimiento;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Seeder;

class AcademicoSeeder extends Seeder
{
    public function run(): void
    {
        $programas = collect([
            [
                'nombre_prog' => 'Ciclo Intensivo Ingeniería UMSA',
                'codigo_prog' => 'INT-UMSA-2026',
                'universidad_objetivo_prog' => 'UMSA',
                'carrera_area_prog' => 'Ingenierías',
                'modalidad_prog' => 'Presencial intensiva',
                'fecha_inicio_prog' => '2026-06-15',
                'fecha_fin_prog' => '2026-09-30',
                'descripcion_prog' => 'Preparación intensiva en Matemática, Física y Química orientada a procesos de admisión de Ingeniería.',
            ],
            [
                'nombre_prog' => 'Nivelación Ciencias Exactas EMI',
                'codigo_prog' => 'NIV-EMI-2026',
                'universidad_objetivo_prog' => 'EMI',
                'carrera_area_prog' => 'Ingenierías',
                'modalidad_prog' => 'Presencial',
                'fecha_inicio_prog' => '2026-07-01',
                'fecha_fin_prog' => '2026-10-15',
                'descripcion_prog' => 'Ciclo de nivelación para fortalecer resolución matemática y fundamentos de ciencias exactas.',
            ],
            [
                'nombre_prog' => 'Preparación PAA Ingeniería UCB/UPB',
                'codigo_prog' => 'PAA-UCB-UPB',
                'universidad_objetivo_prog' => 'UCB / UPB',
                'carrera_area_prog' => 'Ingeniería y área empresarial',
                'modalidad_prog' => 'Híbrida',
                'fecha_inicio_prog' => '2026-06-20',
                'fecha_fin_prog' => '2026-09-20',
                'descripcion_prog' => 'Preparación cuantitativa y de razonamiento académico para pruebas de aptitud.',
            ],
            [
                'nombre_prog' => 'Ciclo Regular Ingeniería UPEA',
                'codigo_prog' => 'REG-UPEA-2026',
                'universidad_objetivo_prog' => 'UPEA',
                'carrera_area_prog' => 'Ingenierías',
                'modalidad_prog' => 'Presencial regular',
                'fecha_inicio_prog' => '2026-06-10',
                'fecha_fin_prog' => '2026-11-10',
                'descripcion_prog' => 'Ciclo regular para consolidar contenidos curriculares y práctica sostenida.',
            ],
            [
                'nombre_prog' => 'Refuerzo Matemática, Física y Química',
                'codigo_prog' => 'REF-STEM-2026',
                'universidad_objetivo_prog' => 'Múltiples universidades',
                'carrera_area_prog' => 'Ciencias exactas e Ingeniería',
                'modalidad_prog' => 'Semipresencial',
                'fecha_inicio_prog' => '2026-07-05',
                'fecha_fin_prog' => '2026-10-05',
                'descripcion_prog' => 'Ruta focalizada de refuerzo por materia para postulantes que requieren nivelación específica.',
            ],
        ])->mapWithKeys(function (array $data) {
            $programa = ProgramaAcademico::updateOrCreate(
                ['codigo_prog' => $data['codigo_prog']],
                [...$data, 'estado_prog' => 'activo'],
            );

            return [$data['codigo_prog'] => $programa];
        });

        $grupos = collect([
            ['programa' => 'INT-UMSA-2026', 'nombre_grupo' => 'Paralelo Matemática A', 'codigo_grupo' => 'UMSA-MAT-A', 'turno_grupo' => 'Mañana', 'aula_grupo' => 'A-201', 'capacidad_grupo' => 30, 'nivel_grupo' => 'Intensivo', 'tutor_responsable_grupo' => 'Lic. Daniela Vargas'],
            ['programa' => 'INT-UMSA-2026', 'nombre_grupo' => 'Paralelo Física B', 'codigo_grupo' => 'UMSA-FIS-B', 'turno_grupo' => 'Tarde', 'aula_grupo' => 'B-105', 'capacidad_grupo' => 28, 'nivel_grupo' => 'Intensivo', 'tutor_responsable_grupo' => 'Ing. Marcelo Quispe'],
            ['programa' => 'NIV-EMI-2026', 'nombre_grupo' => 'Intensivo EMI A', 'codigo_grupo' => 'EMI-INT-A', 'turno_grupo' => 'Mañana', 'aula_grupo' => 'LAB-02', 'capacidad_grupo' => 25, 'nivel_grupo' => 'Nivelación', 'tutor_responsable_grupo' => 'Lic. Paola Rojas'],
            ['programa' => 'PAA-UCB-UPB', 'nombre_grupo' => 'PAA UCB A', 'codigo_grupo' => 'PAA-UCB-A', 'turno_grupo' => 'Tarde', 'aula_grupo' => 'C-301', 'capacidad_grupo' => 30, 'nivel_grupo' => 'Intermedio', 'tutor_responsable_grupo' => 'Lic. Álvaro Mendoza'],
            ['programa' => 'REG-UPEA-2026', 'nombre_grupo' => 'Sistemas UPEA A', 'codigo_grupo' => 'UPEA-SIS-A', 'turno_grupo' => 'Noche', 'aula_grupo' => 'D-104', 'capacidad_grupo' => 35, 'nivel_grupo' => 'Regular', 'tutor_responsable_grupo' => 'Ing. Natalia Condori'],
        ])->mapWithKeys(function (array $data) use ($programas) {
            $programa = $programas[$data['programa']];
            unset($data['programa']);
            $grupo = GrupoAcademico::updateOrCreate(
                ['id_prog' => $programa->id_prog, 'codigo_grupo' => $data['codigo_grupo']],
                [...$data, 'estado_grupo' => 'activo'],
            );

            return [$data['codigo_grupo'] => $grupo];
        });

        $plantillas = PlantillaEvaluacion::query()->where('estado_plan', 'activa')->orderBy('id_plan')->get();
        $simulacros = [
            ['codigo' => 'UMSA-MAT-A', 'titulo' => 'Diagnóstico Inicial', 'dias' => 7, 'plantilla' => 0, 'modalidad' => 'Presencial'],
            ['codigo' => 'UMSA-MAT-A', 'titulo' => 'Simulacro Matemática Preuniversitaria', 'dias' => 21, 'plantilla' => 1, 'modalidad' => 'Presencial'],
            ['codigo' => 'UMSA-FIS-B', 'titulo' => 'Simulacro Física Mecánica', 'dias' => 28, 'plantilla' => 2, 'modalidad' => 'Presencial'],
            ['codigo' => 'EMI-INT-A', 'titulo' => 'Simulacro Química General', 'dias' => 35, 'plantilla' => 3, 'modalidad' => 'Presencial'],
            ['codigo' => 'PAA-UCB-A', 'titulo' => 'Simulacro Mixto STEM', 'dias' => 42, 'plantilla' => 4, 'modalidad' => 'Híbrida'],
            ['codigo' => 'UPEA-SIS-A', 'titulo' => 'Simulacro Final Preuniversitario', 'dias' => 56, 'plantilla' => 5, 'modalidad' => 'Presencial'],
        ];

        foreach ($simulacros as $item) {
            $grupo = $grupos[$item['codigo']];
            SimulacroProgramado::updateOrCreate(
                ['id_prog' => $grupo->id_prog, 'titulo_sim' => $item['titulo']],
                [
                    'id_grupo' => $grupo->id_grupo,
                    'id_plantilla' => $plantillas->get($item['plantilla'])?->id_plan,
                    'fecha_sim' => today()->addDays($item['dias']),
                    'hora_inicio_sim' => '09:00',
                    'hora_fin_sim' => '11:00',
                    'modalidad_sim' => $item['modalidad'],
                    'estado_sim' => 'programado',
                    'observacion_sim' => 'Simulacro programado para seguimiento académico institucional.',
                ],
            );
        }

        $postulantes = Postulante::query()
            ->where('estado_post', 'activo')
            ->orderBy('id_post')
            ->take(40)
            ->get();

        if ($postulantes->isEmpty()) {
            return;
        }

        $gruposLista = $grupos->values();
        foreach ($postulantes as $index => $postulante) {
            $grupo = $gruposLista[$index % $gruposLista->count()];
            InscripcionAcademica::updateOrCreate(
                ['id_prog' => $grupo->id_prog, 'id_post' => $postulante->id_post],
                [
                    'id_grupo' => $grupo->id_grupo,
                    'fecha_inscripcion' => today()->subDays(20 - ($index % 15)),
                    'estado_inscripcion' => 'activo',
                    'observacion_inscripcion' => 'Inscripción activa para seguimiento académico.',
                ],
            );

            $promedio = 45 + (($index * 7) % 51);
            $asistencia = 65 + (($index * 9) % 36);
            $nivel = NivelSeguimiento::desdePromedio($promedio)->value;

            RendimientoPostulante::updateOrCreate(
                ['id_post' => $postulante->id_post, 'id_prog' => $grupo->id_prog],
                [
                    'id_grupo' => $grupo->id_grupo,
                    'promedio_general_rend' => $promedio,
                    'promedio_matematica_rend' => max(40, min(98, $promedio - 4 + ($index % 8))),
                    'promedio_fisica_rend' => max(40, min(98, $promedio - 2 + (($index * 2) % 7))),
                    'promedio_quimica_rend' => max(40, min(98, $promedio + (($index * 3) % 6))),
                    'promedio_paa_rend' => max(40, min(98, $promedio + 3 - ($index % 5))),
                    'asistencia_porcentaje_rend' => $asistencia,
                    'nivel_riesgo_rend' => $nivel,
                    'observacion_rend' => match ($nivel) {
                        'Alto rendimiento' => 'Desempeño consistente y favorable en el ciclo de nivelación.',
                        'Seguimiento regular' => 'Evolución estable con contenidos específicos por consolidar.',
                        default => 'Requiere acompañamiento tutorial y refuerzo focalizado por materia.',
                    },
                ],
            );
        }
    }
}
