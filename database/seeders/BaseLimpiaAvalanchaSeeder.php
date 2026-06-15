<?php

namespace Database\Seeders;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Resultados\Actions\EnviarRespuestasEvaluacionAction;
use App\Domains\Resultados\Actions\IniciarEvaluacionAplicadaAction;
use App\Domains\Resultados\DTOs\EnviarEvaluacionData;
use App\Domains\Resultados\DTOs\RespuestaEvaluacionData;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Support\AvalanchaAcademicCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseLimpiaAvalanchaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndUsersSeeder::class);

        [$universidades, $carreras, $colegios] = $this->seedInstitutionalCatalogs();
        [$materias, $preguntas] = $this->seedCurriculum();
        $plantillas = $this->seedTemplates($preguntas);
        [$programas, $grupos] = $this->seedProgramsAndGroups();
        $tutores = $this->seedTutorsAndAssignments($programas, $grupos);
        $postulantes = $this->seedApplicants($carreras, $colegios);
        $inscripciones = $this->seedEnrollments($postulantes, $grupos);
        $this->seedAdministrativeStatus($inscripciones);
        $this->seedAttendance($inscripciones, $tutores);
        $simulacros = $this->seedSimulations($programas, $grupos, $plantillas);
        $this->seedAppliedEvaluations($inscripciones, $plantillas, $simulacros);
        $this->syncAttendanceWithPerformance($postulantes);
    }

    private function seedInstitutionalCatalogs(): array
    {
        $universidades = collect([
            ['Universidad Mayor de San Andrés', 'UMSA', 'pública', 'La Paz', 'alta'],
            ['Universidad Pública de El Alto', 'UPEA', 'pública', 'El Alto', 'media-alta'],
            ['Escuela Militar de Ingeniería', 'EMI', 'pública', 'La Paz', 'alta'],
            ['Universidad Católica Boliviana', 'UCB', 'privada', 'La Paz', 'media-alta'],
            ['Universidad Privada Boliviana', 'UPB', 'privada', 'La Paz', 'media-alta'],
        ])->mapWithKeys(function (array $item) {
            $universidad = Universidad::updateOrCreate(
                ['sigla_uni' => $item[1]],
                [
                    'nombre_uni' => $item[0],
                    'tipo_uni' => $item[2],
                    'departamento_uni' => $item[3],
                    'nivel_exigencia_matematica_uni' => $item[4],
                    'estado_uni' => 'activo',
                ],
            );

            return [$item[1] => $universidad];
        });

        $careerRows = [
            'UMSA' => [
                ['Ingeniería Civil', 'Ingeniería', 'alta'],
                ['Ingeniería Industrial', 'Ingeniería', 'alta'],
                ['Ingeniería Química', 'Ingeniería', 'alta'],
                ['Ingeniería Electrónica', 'Ingeniería', 'alta'],
                ['Ingeniería Eléctrica', 'Ingeniería', 'alta'],
                ['Ingeniería Mecánica y Electromecánica', 'Ingeniería', 'alta'],
                ['Ingeniería Petrolera', 'Ingeniería', 'alta'],
                ['Ingeniería Ambiental', 'Ingeniería', 'media-alta'],
                ['Ingeniería de Alimentos', 'Ingeniería', 'media-alta'],
                ['Ingeniería Metalúrgica y Materiales', 'Ingeniería', 'alta'],
                ['Mecatrónica', 'Ingeniería', 'alta'],
                ['Ingeniería Petroquímica', 'Ingeniería', 'alta'],
                ['Ingeniería en Producción Industrial', 'Ingeniería', 'alta'],
                ['Ingeniería de Sistemas', 'Ingeniería', 'alta'],
                ['Ingeniería Informática', 'Ingeniería', 'alta'],
                ['Arquitectura', 'Diseño y tecnología', 'media-alta'],
                ['Medicina', 'Ciencias de la salud', 'media-alta'],
                ['Bioquímica', 'Ciencias de la salud', 'media-alta'],
            ],
            'UPEA' => [
                ['Ingeniería de Sistemas', 'Ingeniería', 'media-alta'],
                ['Ingeniería Civil', 'Ingeniería', 'media-alta'],
            ],
            'EMI' => [
                ['Ingeniería de Sistemas', 'Ingeniería', 'alta'],
                ['Ingeniería Comercial', 'Empresarial', 'media-alta'],
            ],
            'UCB' => [
                ['Ingeniería de Sistemas', 'Ingeniería', 'media-alta'],
                ['Ingeniería Comercial', 'Empresarial', 'media'],
            ],
            'UPB' => [
                ['Ingeniería Industrial', 'Ingeniería', 'media-alta'],
                ['Ingeniería Comercial', 'Empresarial', 'media'],
            ],
        ];
        $carreras = collect();

        foreach ($careerRows as $sigla => $rows) {
            foreach ($rows as [$nombre, $area, $nivel]) {
                $carrera = Carrera::updateOrCreate(
                    ['id_uni' => $universidades[$sigla]->id_uni, 'nombre_car' => $nombre],
                    [
                        'area_car' => $area,
                        'nivel_exigencia_matematica_car' => $nivel,
                        'estado_car' => 'activo',
                    ],
                );
                $carreras->put("{$sigla}|{$nombre}", $carrera);
            }
        }

        $colegios = collect([
            ['Colegio Nacional La Paz', 'Fiscal', 'Centro, La Paz'],
            ['Unidad Educativa San Gabriel', 'Convenio', 'Achumani, La Paz'],
            ['Unidad Educativa Simón Bolívar', 'Fiscal', 'Miraflores, La Paz'],
            ['Colegio Técnico Industrial Pedro Domingo Murillo', 'Fiscal', 'Villa Fátima, La Paz'],
            ['Unidad Educativa Franz Tamayo', 'Fiscal', 'Sopocachi, La Paz'],
            ['Colegio Don Bosco El Prado', 'Convenio', 'Centro, La Paz'],
            ['Unidad Educativa Villa Fátima', 'Fiscal', 'Villa Fátima, La Paz'],
            ['Colegio Santa María de los Ángeles', 'Privado', 'Calacoto, La Paz'],
            ['Unidad Educativa Germán Busch', 'Fiscal', 'El Alto'],
            ['Colegio Particular Los Andes', 'Privado', 'Obrajes, La Paz'],
        ])->mapWithKeys(function (array $item) {
            $colegio = Colegio::updateOrCreate(
                ['nombre_col' => $item[0]],
                [
                    'tipo_col' => $item[1],
                    'ubicacion_col' => $item[2],
                    'estado_col' => 'activo',
                ],
            );

            return [$item[0] => $colegio];
        });

        return [$universidades, $carreras, $colegios];
    }

    private function seedCurriculum(): array
    {
        $materias = collect();
        $temas = collect();
        $preguntas = collect();

        foreach (AvalanchaAcademicCatalog::subjects() as $code => $subject) {
            $materia = Materia::updateOrCreate(
                ['codigo_mat' => $code],
                [
                    'nombre_mat' => $subject['name'],
                    'descripcion_mat' => $subject['description'],
                    'color_mat' => $subject['color'],
                    'icono_mat' => $subject['icon'],
                    'estado_mat' => 'activo',
                ],
            );
            $materias->put($code, $materia);

            foreach ($subject['areas'] as $areaName => $topicRows) {
                $area = AreaConocimiento::updateOrCreate(
                    ['nombre_area' => $areaName],
                    [
                        'id_mat' => $materia->id_mat,
                        'descripcion_area' => "Área curricular de {$subject['name']} para preparación preuniversitaria.",
                        'estado_area' => 'activo',
                    ],
                );

                foreach ($topicRows as $topicRow) {
                    $tema = Tema::updateOrCreate(
                        ['id_area' => $area->id_area, 'nombre_tem' => $topicRow['name']],
                        [
                            'descripcion_tem' => "Contenido evaluable de {$topicRow['name']}.",
                            'nivel_tem' => 'preuniversitario',
                            'estado_tem' => 'activo',
                        ],
                    );
                    $temas->put("{$code}|{$topicRow['name']}", $tema);
                }
            }

            foreach (AvalanchaAcademicCatalog::questions($code, $subject) as $index => $row) {
                $tema = $temas["{$code}|{$row['name']}"];
                $pregunta = Pregunta::updateOrCreate(
                    ['enunciado_preg' => $row['statement']],
                    [
                        'id_tem' => $tema->id_tem,
                        'subtema_preg' => $row['name'],
                        'tipo_preg' => 'opcion_multiple',
                        'dificultad_preg' => $row['difficulty'],
                        'exigencia_preg' => $code === 'PAA' ? 'Aptitud académica' : 'Ingreso a Ingeniería',
                        'habilidad_preg' => $this->abilityFor($code, $index),
                        'tiempo_estimado_seg_preg' => match ($row['difficulty']) {
                            'basica' => 75,
                            'media' => 110,
                            default => 150,
                        },
                        'relacion_ingenieria_preg' => $code === 'PAA'
                            ? 'Fortalece comprensión, lógica y toma de decisiones.'
                            : 'Contenido base para resolución de problemas de Ingeniería.',
                        'puntaje_preg' => 10,
                        'explicacion_preg' => $row['explanation'],
                        'estado_preg' => 'activo',
                    ],
                );

                $options = array_merge([$row['correct']], $row['distractors']);
                $rotation = ($index + $row['variant']) % count($options);
                $options = array_merge(array_slice($options, $rotation), array_slice($options, 0, $rotation));

                foreach ($options as $optionIndex => $text) {
                    $pregunta->alternativas()->updateOrCreate(
                        ['letra_alt' => chr(65 + $optionIndex)],
                        [
                            'texto_alt' => $text,
                            'es_correcta_alt' => $text === $row['correct'],
                            'orden_alt' => $optionIndex + 1,
                            'estado_alt' => 'activo',
                        ],
                    );
                }

                $preguntas->push($pregunta->load('tema.area.materia', 'alternativas'));
            }
        }

        return [$materias, $preguntas];
    }

    private function seedTemplates(Collection $questions): \Illuminate\Support\Collection
    {
        $templates = collect();

        foreach (['MAT' => 'Matemática', 'FIS' => 'Física', 'QMC' => 'Química'] as $code => $name) {
            for ($partial = 1; $partial <= 3; $partial++) {
                $selected = $this->selectByDifficulty($questions, $code, ($partial - 1) * 3);
                $template = $this->upsertTemplate(
                    "{$name} Parcial {$partial}",
                    "Evaluación parcial {$partial} de {$name} para el Prefacultativo de Ingeniería UMSA.",
                    'Medir avance curricular por parcial y orientar el refuerzo académico.',
                    75,
                    'mixta',
                    $selected,
                );
                $templates->put("{$code}-P{$partial}", $template);
            }
        }

        $integral = $this->subjectQuestions($questions, 'MAT')->take(8)
            ->concat($this->subjectQuestions($questions, 'FIS')->take(6))
            ->concat($this->subjectQuestions($questions, 'QMC')->take(6));
        $templates->put('SUF-INT', $this->upsertTemplate(
            'Evaluación Integral Ingeniería UMSA',
            'Evaluación única de Prueba de Suficiencia con 40% Matemática, 30% Física y 30% Química.',
            'Valorar preparación integral para la modalidad de Prueba de Suficiencia.',
            120,
            'avanzada',
            $integral,
        ));

        $diagnostic = $this->subjectQuestions($questions, 'MAT')->slice(10, 5)
            ->concat($this->subjectQuestions($questions, 'FIS')->slice(10, 5))
            ->concat($this->subjectQuestions($questions, 'QMC')->slice(10, 5));
        $templates->put('DIAG', $this->upsertTemplate(
            'Diagnóstico Inicial Ingeniería',
            'Lectura inicial de conocimientos en Matemática, Física y Química.',
            'Identificar fortalezas y necesidades de nivelación al inicio del ciclo.',
            90,
            'basica-media',
            $diagnostic,
        ));

        $final = $this->subjectQuestions($questions, 'MAT')->slice(20, 8)
            ->concat($this->subjectQuestions($questions, 'FIS')->slice(18, 6))
            ->concat($this->subjectQuestions($questions, 'QMC')->slice(18, 6));
        $templates->put('PF-FINAL', $this->upsertTemplate(
            'Simulacro Final Prefacultativo',
            'Cierre integral del ciclo Prefacultativo de Ingeniería.',
            'Consolidar una lectura final por materias antes del proceso de admisión.',
            120,
            'avanzada',
            $final,
        ));
        $templates->put('SUF-SIM', $this->upsertTemplate(
            'Simulacro Integral Prueba de Suficiencia',
            'Simulación integral previa a la Prueba de Suficiencia de Ingeniería.',
            'Fortalecer gestión de tiempo y resolución interdisciplinaria.',
            120,
            'avanzada',
            $integral->reverse()->values(),
        ));

        return $templates;
    }

    private function seedProgramsAndGroups(): array
    {
        $programs = collect([
            [
                'PF-UMSA-I-2026',
                'Prefacultativo Ingeniería UMSA - Gestión I/2026',
                'Universidad Mayor de San Andrés',
                'Facultad de Ingeniería',
                'Prefacultativo',
                '2026-02-02',
                '2026-07-10',
                'Ciclo semestral con tres parciales de Matemática, Física y Química.',
            ],
            [
                'PS-UMSA-2026',
                'Prueba de Suficiencia Ingeniería UMSA - Intensivo 2026',
                'Universidad Mayor de San Andrés',
                'Facultad de Ingeniería',
                'Prueba de Suficiencia',
                '2026-05-04',
                '2026-08-15',
                'Preparación intensiva para una evaluación integral de Matemática, Física y Química.',
            ],
            [
                'NIV-ING-LP-2026',
                'Nivelación General Ingeniería - Universidades La Paz',
                'UMSA, UPEA, EMI, UCB y UPB',
                'Ingeniería',
                'Nivelación',
                '2026-03-02',
                '2026-09-30',
                'Nivelación en ciencias exactas para postulantes a universidades de La Paz.',
            ],
            [
                'INT-MF-2026',
                'Intensivo Matemática y Física para Ingeniería',
                'Universidades de La Paz',
                'Ingeniería',
                'Refuerzo académico',
                '2026-05-18',
                '2026-07-31',
                'Refuerzo operativo de Matemática y Física para procesos de admisión.',
            ],
        ])->mapWithKeys(function (array $row) {
            $program = ProgramaAcademico::updateOrCreate(
                ['codigo_prog' => $row[0]],
                [
                    'nombre_prog' => $row[1],
                    'universidad_objetivo_prog' => $row[2],
                    'carrera_area_prog' => $row[3],
                    'modalidad_prog' => $row[4],
                    'fecha_inicio_prog' => $row[5],
                    'fecha_fin_prog' => $row[6],
                    'descripcion_prog' => $row[7],
                    'estado_prog' => 'activo',
                ],
            );

            return [$row[0] => $program];
        });

        $groupRows = [
            ['PF-UMSA-I-2026', 'PF-ING-MA-A', 'Prefacultativo Ingeniería Mañana A', 'Mañana', 'Aula 101', 30],
            ['PF-UMSA-I-2026', 'PF-ING-TA-B', 'Prefacultativo Ingeniería Tarde B', 'Tarde', 'Aula 202', 30],
            ['PF-UMSA-I-2026', 'PF-ING-NO-C', 'Prefacultativo Ingeniería Noche C', 'Noche', 'Laboratorio 1', 30],
            ['PS-UMSA-2026', 'PS-ING-FS-A', 'Prueba de Suficiencia Fin de Semana A', 'Fin de Semana', 'Aula 301', 25],
            ['NIV-ING-LP-2026', 'NIV-ING-TA-A', 'Nivelación Ingeniería Tarde A', 'Tarde', 'Aula 204', 35],
            ['INT-MF-2026', 'INT-MF-NO-A', 'Intensivo Matemática y Física Noche A', 'Noche', 'Aula 105', 25],
        ];
        $groups = collect();

        foreach ($groupRows as $row) {
            $group = GrupoAcademico::updateOrCreate(
                ['id_prog' => $programs[$row[0]]->id_prog, 'codigo_grupo' => $row[1]],
                [
                    'nombre_grupo' => $row[2],
                    'turno_grupo' => $row[3],
                    'aula_grupo' => $row[4],
                    'capacidad_grupo' => $row[5],
                    'nivel_grupo' => 'Preuniversitario',
                    'tutor_responsable_grupo' => 'Coordinación Académica Avalancha',
                    'estado_grupo' => 'activo',
                ],
            );
            $groups->put($row[1], $group);
        }

        return [$programs, $groups];
    }

    private function seedTutorsAndAssignments(Collection $programs, Collection $groups): Collection
    {
        $rows = [
            [
                'rodrigo.salazar@avalancha.edu.bo',
                'Rodrigo',
                'Salazar Condori',
                '7300101',
                'Matemática',
                'Licenciatura en Matemática',
            ],
            [
                'carla.mendoza@avalancha.edu.bo',
                'Carla',
                'Mendoza Rojas',
                '7300102',
                'Física',
                'Ingeniería Física',
            ],
            [
                'luis.arce@avalancha.edu.bo',
                'Luis Fernando',
                'Arce Huanca',
                '7300103',
                'Química',
                'Licenciatura en Química',
            ],
            [
                'patricia.vargas@avalancha.edu.bo',
                'Patricia',
                'Vargas Choque',
                '7300104',
                'Razonamiento Lógico / Coordinación Académica',
                'Ciencias de la Educación',
            ],
        ];
        $tutors = collect();

        foreach ($rows as $index => $row) {
            $user = User::where('email', $row[0])->firstOrFail();
            $tutor = TutorAcademico::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres_tutor' => $row[1],
                    'apellidos_tutor' => $row[2],
                    'ci_tutor' => $row[3],
                    'celular_tutor' => '72010'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'correo_tutor' => $row[0],
                    'especialidad_tutor' => $row[4],
                    'formacion_tutor' => $row[5],
                    'experiencia_tutor' => 'Experiencia en preparación preuniversitaria y acompañamiento académico para Ingeniería.',
                    'estado_tutor' => 'activo',
                    'observacion_tutor' => 'Personal académico de Academia Universitaria Avalancha.',
                ],
            );
            $tutors->put($row[4], $tutor);
        }

        $subjectTutors = [
            'Matemática' => $tutors['Matemática'],
            'Física' => $tutors['Física'],
            'Química' => $tutors['Química'],
        ];
        $targetGroups = $groups->only([
            'PF-ING-MA-A',
            'PF-ING-TA-B',
            'PF-ING-NO-C',
            'PS-ING-FS-A',
        ]);

        foreach ($targetGroups as $group) {
            foreach ($subjectTutors as $subject => $tutor) {
                AsignacionTutor::updateOrCreate(
                    [
                        'id_tutor' => $tutor->id_tutor,
                        'id_prog' => $group->id_prog,
                        'id_grupo' => $group->id_grupo,
                        'materia_referencia_asig' => $subject,
                    ],
                    [
                        'rol_asig' => 'Tutor Académico',
                        'fecha_inicio_asig' => '2026-02-02',
                        'fecha_fin_asig' => '2026-08-15',
                        'estado_asig' => 'activo',
                        'observacion_asig' => "Acompañamiento de {$subject} para el grupo {$group->codigo_grupo}.",
                    ],
                );
            }
        }

        foreach ($programs as $program) {
            AsignacionTutor::updateOrCreate(
                [
                    'id_tutor' => $tutors['Razonamiento Lógico / Coordinación Académica']->id_tutor,
                    'id_prog' => $program->id_prog,
                    'id_grupo' => null,
                    'materia_referencia_asig' => 'Coordinación Académica',
                ],
                [
                    'rol_asig' => 'Coordinación Académica',
                    'fecha_inicio_asig' => $program->fecha_inicio_prog,
                    'fecha_fin_asig' => $program->fecha_fin_prog,
                    'estado_asig' => 'activo',
                    'observacion_asig' => 'Seguimiento integral del programa y coordinación tutorial.',
                ],
            );
        }

        return $tutors;
    }

    private function seedApplicants(Collection $careers, Collection $schools): Collection
    {
        $givenNames = [
            'Valeria', 'Diego', 'Mariana', 'Alejandro', 'Camila', 'Santiago',
            'Lucía', 'Mateo', 'Fernanda', 'Nicolás', 'Daniela', 'Joaquín',
            'Andrea', 'Sebastián', 'Paola', 'Gabriel', 'Natalia', 'Martín',
            'Brenda', 'Álvaro', 'Roxana', 'Miguel', 'Carolina', 'Esteban',
        ];
        $surnames = [
            'Choque', 'Mamani', 'Quispe', 'Flores', 'Condori', 'Rojas',
            'Huanca', 'Apaza', 'Vargas', 'Mendoza', 'Salazar', 'Torrez',
            'Nina', 'Callisaya', 'Laura', 'Poma', 'Ticona', 'Cruz',
            'Miranda', 'Gutiérrez', 'Alarcón', 'Fernández', 'López', 'Ramos',
        ];
        $turns = ['Mañana', 'Tarde', 'Noche', 'Fin de Semana'];
        $schoolList = $schools->values();
        $postulantes = collect();

        for ($index = 0; $index < 72; $index++) {
            [$names, $lastNames, $email] = $this->applicantIdentity($index, $givenNames, $surnames);
            $career = $this->careerForApplicant($index, $careers);
            $school = $schoolList[$index % $schoolList->count()];
            $postulante = Postulante::updateOrCreate(
                ['ci_post' => (string) (9100000 + $index)],
                [
                    'nombres_post' => $names,
                    'apellidos_post' => $lastNames,
                    'email_post' => $email,
                    'celular_post' => '765'.str_pad((string) (10000 + $index), 5, '0', STR_PAD_LEFT),
                    'edad_post' => 17 + ($index % 5),
                    'id_col' => $school->id_col,
                    'id_car' => $career->id_car,
                    'turno_post' => $turns[$index % count($turns)],
                    'gestion_post' => 2026,
                    'estado_post' => $index % 19 === 0 && $index > 2 ? 'inactivo' : 'activo',
                    'observaciones_post' => $index < 51
                        ? 'Postulación prioritaria a la Facultad de Ingeniería UMSA.'
                        : 'Preparación preuniversitaria en ciencias exactas.',
                ],
            );
            $postulantes->push($postulante);
        }

        return $postulantes;
    }

    private function seedEnrollments(Collection $postulantes, Collection $groups): Collection
    {
        $inscripciones = collect();
        $groupDistribution = [
            ...array_fill(0, 12, 'PF-ING-MA-A'),
            ...array_fill(0, 12, 'PF-ING-TA-B'),
            ...array_fill(0, 12, 'PF-ING-NO-C'),
            ...array_fill(0, 12, 'PS-ING-FS-A'),
            ...array_fill(0, 12, 'NIV-ING-TA-A'),
            ...array_fill(0, 12, 'INT-MF-NO-A'),
        ];

        foreach ($postulantes->values() as $index => $postulante) {
            $group = $groups[$groupDistribution[$index]];
            $status = $index % 17 === 0 && $index > 2 ? 'inactivo' : 'activo';
            $reason = match (true) {
                $index % 29 === 0 && $index > 2 => 'Retiro académico registrado durante el ciclo.',
                $index % 17 === 0 && $index > 2 => 'Ciclo académico finalizado.',
                $index % 13 === 0 => 'Inscripción con seguimiento administrativo.',
                default => 'Inscripción académica vigente.',
            };
            $inscripcion = InscripcionAcademica::updateOrCreate(
                ['id_prog' => $group->id_prog, 'id_post' => $postulante->id_post],
                [
                    'id_grupo' => $group->id_grupo,
                    'fecha_inscripcion' => Carbon::parse('2026-01-20')->addDays($index % 45),
                    'estado_inscripcion' => $status,
                    'observacion_inscripcion' => $reason,
                ],
            );
            $inscripciones->push($inscripcion->load('postulante', 'programa', 'grupo'));
        }

        return $inscripciones;
    }

    private function seedAdministrativeStatus(Collection $inscripciones): void
    {
        foreach ($inscripciones->values() as $index => $inscripcion) {
            $profile = match ($index) {
                0 => 'current',
                1 => 'pending',
                2 => 'overdue',
                default => match ($index % 10) {
                    0, 1, 2, 3, 4, 5 => 'current',
                    6, 7 => 'pending',
                    8 => 'overdue',
                    default => $index % 20 === 9 ? 'scholarship' : 'exempt',
                },
            };
            $matriculaState = match ($profile) {
                'overdue' => 'observada',
                'scholarship' => 'becada',
                'exempt' => 'exenta',
                default => 'activa',
            };
            $matricula = MatriculaAcademica::updateOrCreate(
                ['id_insc' => $inscripcion->id_insc],
                [
                    'id_post' => $inscripcion->id_post,
                    'id_prog' => $inscripcion->id_prog,
                    'id_grupo' => $inscripcion->id_grupo,
                    'codigo_mat' => sprintf('AVA-2026-%04d', $inscripcion->id_insc),
                    'fecha_matricula_mat' => $inscripcion->fecha_inscripcion,
                    'monto_matricula_mat' => $profile === 'exempt' ? 0 : 350,
                    'estado_matricula_mat' => $matriculaState,
                    'tipo_beneficio_mat' => match ($profile) {
                        'scholarship' => 'Beca académica parcial',
                        'exempt' => 'Exención institucional',
                        default => null,
                    },
                    'observacion_mat' => 'Control administrativo interno de Academia Universitaria Avalancha.',
                ],
            );

            $installments = $inscripcion->programa->modalidad_prog === 'Prefacultativo' ? 3 : 2;
            for ($number = 1; $number <= $installments; $number++) {
                $dueDate = $profile === 'pending'
                    ? ($number === 1
                        ? Carbon::parse('2026-04-10')
                        : Carbon::parse('2026-07-10')->addMonths($number - 2))
                    : Carbon::parse('2026-03-10')->addMonths($number - 1);
                $state = $this->installmentState($profile, $number);
                CuotaAcademica::updateOrCreate(
                    ['id_mat' => $matricula->id_mat, 'nro_cuota' => $number],
                    [
                        'concepto_cuota' => "Cuota académica {$number}",
                        'monto_cuota' => in_array($state, ['becada', 'exenta'], true) ? 0 : 580,
                        'fecha_vencimiento_cuota' => $dueDate,
                        'fecha_pago_cuota' => $state === 'pagada' ? $dueDate->copy()->subDays(3) : null,
                        'metodo_pago_cuota' => $state === 'pagada' ? 'Registro en caja institucional' : null,
                        'estado_cuota' => $state,
                        'observacion_cuota' => $this->installmentNote($state),
                    ],
                );
            }

            $habilitation = match ($profile) {
                'pending' => ['observado', true, true, true, 'Cuota vigente pendiente de regularización.'],
                'overdue' => ['restringido', false, false, true, 'Cuota vencida pendiente de regularización administrativa.'],
                default => ['habilitado', true, true, true, 'Condición administrativa regular.'],
            };
            HabilitacionAcademica::updateOrCreate(
                ['id_insc' => $inscripcion->id_insc],
                [
                    'id_post' => $inscripcion->id_post,
                    'id_mat' => $matricula->id_mat,
                    'estado_hab' => $habilitation[0],
                    'motivo_hab' => $habilitation[4],
                    'fecha_inicio_hab' => $inscripcion->fecha_inscripcion,
                    'fecha_fin_hab' => $inscripcion->programa->fecha_fin_prog,
                    'habilitado_evaluaciones_hab' => $habilitation[1],
                    'habilitado_simulacros_hab' => $habilitation[2],
                    'habilitado_reportes_hab' => $habilitation[3],
                    'observacion_hab' => 'Estado calculado para seguimiento académico-administrativo.',
                ],
            );
        }
    }

    private function seedAttendance(Collection $inscripciones, Collection $tutors): void
    {
        $sessionDates = collect(range(0, 3))->flatMap(fn (int $week) => [
            Carbon::today()->subWeeks(3 - $week)->startOfWeek()->addDay(),
            Carbon::today()->subWeeks(3 - $week)->startOfWeek()->addDays(3),
        ])->values();

        foreach ($inscripciones->where('estado_inscripcion', 'activo')->values() as $index => $inscripcion) {
            $profile = $this->academicProfile($index);

            foreach ($sessionDates as $sessionIndex => $date) {
                $subject = ['Matemática', 'Física', 'Química'][$sessionIndex % 3];
                $status = $this->attendanceStatus($profile, $sessionIndex);
                AsistenciaAcademica::updateOrCreate(
                    [
                        'id_grupo' => $inscripcion->id_grupo,
                        'id_post' => $inscripcion->id_post,
                        'fecha_asist' => $date->toDateString(),
                        'sesion_asist' => $subject,
                    ],
                    [
                        'id_prog' => $inscripcion->id_prog,
                        'id_tutor' => $tutors[$subject]->id_tutor,
                        'estado_asist' => $status,
                        'observacion_asist' => match ($status) {
                            'ausente' => 'Ausencia registrada para seguimiento tutorial.',
                            'retraso' => 'Ingreso posterior al inicio de la sesión.',
                            'justificado' => 'Inasistencia respaldada por coordinación académica.',
                            default => 'Participación regular en la sesión.',
                        },
                    ],
                );
            }
        }
    }

    private function seedSimulations(
        Collection $programs,
        Collection $groups,
        Collection $templates,
    ): Collection {
        $rows = [
            ['PF-UMSA-I-2026', 'PF-ING-MA-A', 'MAT-P1', 'Matemática Parcial 1', '2026-03-21', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-MA-A', 'MAT-P2', 'Matemática Parcial 2', '2026-05-09', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-MA-A', 'MAT-P3', 'Matemática Parcial 3', '2026-07-04', 'programado'],
            ['PF-UMSA-I-2026', 'PF-ING-TA-B', 'FIS-P1', 'Física Parcial 1', '2026-03-28', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-TA-B', 'FIS-P2', 'Física Parcial 2', '2026-05-16', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-TA-B', 'FIS-P3', 'Física Parcial 3', '2026-07-06', 'programado'],
            ['PF-UMSA-I-2026', 'PF-ING-NO-C', 'QMC-P1', 'Química Parcial 1', '2026-04-04', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-NO-C', 'QMC-P2', 'Química Parcial 2', '2026-05-23', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-NO-C', 'QMC-P3', 'Química Parcial 3', '2026-07-08', 'programado'],
            ['PS-UMSA-2026', 'PS-ING-FS-A', 'SUF-SIM', 'Simulacro Integral Ingeniería UMSA', '2026-05-31', 'cerrado'],
            ['NIV-ING-LP-2026', 'NIV-ING-TA-A', 'DIAG', 'Diagnóstico Inicial', '2026-04-18', 'cerrado'],
            ['PF-UMSA-I-2026', 'PF-ING-MA-A', 'PF-FINAL', 'Simulacro Final Prefacultativo', '2026-07-11', 'programado'],
        ];
        $simulations = collect();

        foreach ($rows as $row) {
            $simulation = SimulacroProgramado::updateOrCreate(
                ['id_prog' => $programs[$row[0]]->id_prog, 'titulo_sim' => $row[3]],
                [
                    'id_grupo' => $groups[$row[1]]->id_grupo,
                    'id_plantilla' => $templates[$row[2]]->id_plan,
                    'fecha_sim' => $row[4],
                    'hora_inicio_sim' => '08:30',
                    'hora_fin_sim' => '10:30',
                    'modalidad_sim' => 'Presencial',
                    'estado_sim' => $row[5],
                    'observacion_sim' => 'Actividad evaluativa programada por coordinación académica.',
                ],
            );
            $simulations->put($row[3], $simulation);
        }

        return $simulations;
    }

    private function seedAppliedEvaluations(
        Collection $inscripciones,
        Collection $templates,
        Collection $simulations,
    ): void {
        $iniciar = app(IniciarEvaluacionAplicadaAction::class);
        $enviar = app(EnviarRespuestasEvaluacionAction::class);

        foreach ($inscripciones->where('estado_inscripcion', 'activo')->values() as $index => $inscripcion) {
            if ($index % 9 === 8) {
                continue;
            }

            $applications = $this->applicationsFor($inscripcion, $templates, $simulations, $index);
            $profile = $this->academicProfile($index);

            foreach ($applications as [$template, $simulation]) {
                $exists = EvaluacionAplicada::query()
                    ->where('id_post', $inscripcion->id_post)
                    ->where('id_plantilla', $template->id_plan)
                    ->where('estado_eval_apl', 'finalizada')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $template->load('preguntas.alternativas');
                $evaluation = $iniciar->execute(
                    $inscripcion->postulante,
                    $template,
                    $simulation?->id_sim,
                    'evaluacion_institucional',
                );
                $answers = $template->preguntas->values()->map(
                    function (Pregunta $question, int $questionIndex) use ($profile, $index) {
                        $correct = $question->alternativas->firstWhere('es_correcta_alt', true);
                        $incorrect = $question->alternativas->firstWhere('es_correcta_alt', false);
                        $threshold = match ($profile) {
                            'high' => 90,
                            'medium' => 72,
                            default => 48,
                        };
                        $isCorrect = (($questionIndex * 37) + ($index * 13)) % 100 < $threshold;
                        $selected = $isCorrect ? $correct : ($incorrect ?: $correct);

                        return new RespuestaEvaluacionData(
                            preguntaId: $question->id_preg,
                            alternativaId: $selected?->id_alt,
                            respuestaTexto: null,
                            tiempoSegundos: 55 + (($questionIndex + $index) % 5) * 18,
                        );
                    },
                )->all();

                $evaluation = $enviar->execute(
                    $evaluation,
                    $inscripcion->postulante,
                    new EnviarEvaluacionData(
                        respuestas: $answers,
                        tiempoTotalSegundos: collect($answers)->sum('tiempoSegundos'),
                    ),
                );

                $finishedAt = $simulation?->fecha_sim
                    ? Carbon::parse($simulation->fecha_sim)->setTime(10, 30)
                    : Carbon::today()->subDays(20 + ($index % 15))->setTime(10, 30);
                $evaluation->update([
                    'fecha_inicio_eval_apl' => $finishedAt->copy()->subMinutes($template->duracion_minutos_plan),
                    'fecha_fin_eval_apl' => $finishedAt,
                    'observacion_eval_apl' => 'Resultado trazable generado desde respuestas registradas.',
                ]);
            }
        }
    }

    private function syncAttendanceWithPerformance(Collection $postulantes): void
    {
        foreach ($postulantes as $postulante) {
            $attendance = $postulante->asistenciasAcademicas;
            if ($attendance->isEmpty()) {
                continue;
            }

            $percentage = round(
                100 * $attendance->whereIn('estado_asist', ['presente', 'retraso', 'justificado'])->count()
                / $attendance->count(),
                2,
            );

            RendimientoPostulante::query()
                ->where('id_post', $postulante->id_post)
                ->update(['asistencia_porcentaje_rend' => $percentage]);
        }
    }

    private function abilityFor(string $code, int $index): string
    {
        $abilities = match ($code) {
            'MAT' => ['Cálculo operativo', 'Modelado matemático', 'Interpretación gráfica'],
            'FIS' => ['Modelado fenomenológico', 'Cálculo operativo', 'Interpretación física'],
            'QMC' => ['Razonamiento químico', 'Cálculo estequiométrico', 'Análisis conceptual'],
            default => ['Razonamiento lógico', 'Comprensión de problemas', 'Análisis de patrones'],
        };

        return $abilities[$index % count($abilities)];
    }

    private function selectByDifficulty(Collection $questions, string $code, int $offset): Collection
    {
        $subject = $this->subjectQuestions($questions, $code);

        return $this->cycleTake($subject->where('dificultad_preg', 'basica')->values(), $offset, 4)
            ->concat($this->cycleTake($subject->where('dificultad_preg', 'media')->values(), $offset, 4))
            ->concat($this->cycleTake($subject->where('dificultad_preg', 'avanzada')->values(), $offset, 2))
            ->values();
    }

    private function subjectQuestions(Collection $questions, string $code): Collection
    {
        return $questions->filter(
            fn (Pregunta $question) => $question->tema?->area?->materia?->codigo_mat === $code,
        )->values();
    }

    private function cycleTake(Collection $items, int $offset, int $count): Collection
    {
        if ($items->isEmpty()) {
            return collect();
        }

        return collect(range(0, $count - 1))->map(
            fn (int $step) => $items[($offset + $step) % $items->count()],
        );
    }

    private function upsertTemplate(
        string $name,
        string $description,
        string $objective,
        int $duration,
        string $difficulty,
        Collection $questions,
    ): PlantillaEvaluacion {
        $template = PlantillaEvaluacion::updateOrCreate(
            ['nombre_plan' => $name],
            [
                'descripcion_plan' => $description,
                'objetivo_plan' => $objective,
                'duracion_minutos_plan' => $duration,
                'dificultad_plan' => $difficulty,
                'estado_plan' => 'activa',
            ],
        );
        $unit = round(100 / max(1, $questions->count()), 2);
        $assigned = 0.0;
        $pivot = [];

        foreach ($questions->values() as $index => $question) {
            $points = $index === $questions->count() - 1
                ? round(100 - $assigned, 2)
                : $unit;
            $pivot[$question->id_preg] = [
                'orden_pp' => $index + 1,
                'puntaje_pp' => $points,
            ];
            $assigned += $points;
        }

        $template->preguntas()->sync($pivot);

        return $template->load('preguntas.alternativas');
    }

    private function applicantIdentity(int $index, array $givenNames, array $surnames): array
    {
        if ($index === 0) {
            return ['Valeria Nina', 'Choque', 'valeria.nina@postulante.avalancha.edu.bo'];
        }

        if ($index === 1) {
            return ['Diego', 'Mamani Flores', 'diego.mamani@postulante.avalancha.edu.bo'];
        }

        if ($index === 2) {
            return ['Mariana', 'Quispe Rojas', 'mariana.quispe@postulante.avalancha.edu.bo'];
        }

        $first = $givenNames[$index % count($givenNames)];
        $firstSurname = $surnames[($index * 5) % count($surnames)];
        $secondSurname = $surnames[(($index * 7) + 3) % count($surnames)];
        $emailName = Str::slug(Str::ascii("{$first}.{$firstSurname}.{$index}"), '.');

        return [$first, "{$firstSurname} {$secondSurname}", "{$emailName}@postulante.avalancha.edu.bo"];
    }

    private function careerForApplicant(int $index, Collection $careers): Carrera
    {
        $umsaEngineering = [
            'Ingeniería Civil',
            'Ingeniería Industrial',
            'Ingeniería Química',
            'Ingeniería Electrónica',
            'Ingeniería Eléctrica',
            'Ingeniería Mecánica y Electromecánica',
            'Ingeniería Petrolera',
            'Ingeniería Ambiental',
            'Mecatrónica',
        ];

        if ($index < 51) {
            return $careers['UMSA|'.$umsaEngineering[$index % count($umsaEngineering)]];
        }

        if ($index < 62) {
            $secondary = [
                'EMI|Ingeniería de Sistemas',
                'EMI|Ingeniería Comercial',
                'UPEA|Ingeniería de Sistemas',
                'UPEA|Ingeniería Civil',
            ];

            return $careers[$secondary[$index % count($secondary)]];
        }

        if ($index < 69) {
            $private = [
                'UCB|Ingeniería de Sistemas',
                'UCB|Ingeniería Comercial',
                'UPB|Ingeniería Industrial',
                'UPB|Ingeniería Comercial',
            ];

            return $careers[$private[$index % count($private)]];
        }

        return $careers[
            ['UMSA|Arquitectura', 'UMSA|Medicina', 'UMSA|Bioquímica'][$index % 3]
        ];
    }

    private function installmentState(string $profile, int $number): string
    {
        return match ($profile) {
            'current' => 'pagada',
            'pending' => $number === 1 ? 'pagada' : 'pendiente',
            'overdue' => $number === 1 ? 'pagada' : ($number === 2 ? 'vencida' : 'pendiente'),
            'scholarship' => 'becada',
            'exempt' => 'exenta',
            default => 'pendiente',
        };
    }

    private function installmentNote(string $state): string
    {
        return match ($state) {
            'pagada' => 'Cuota registrada como pagada en control administrativo interno.',
            'pendiente' => 'Cuota vigente pendiente de pago.',
            'vencida' => 'Cuota vencida sujeta a regularización administrativa.',
            'becada' => 'Cuota cubierta por beneficio académico.',
            'exenta' => 'Cuota exenta por disposición institucional.',
            default => 'Seguimiento administrativo.',
        };
    }

    private function academicProfile(int $index): string
    {
        return match (true) {
            $index === 0 => 'high',
            $index === 1 => 'medium',
            $index === 2 => 'low',
            $index % 10 <= 2 => 'high',
            $index % 10 <= 7 => 'medium',
            default => 'low',
        };
    }

    private function attendanceStatus(string $profile, int $session): string
    {
        return match ($profile) {
            'high' => $session === 5 ? 'retraso' : 'presente',
            'medium' => match ($session) {
                2, 7 => 'ausente',
                6 => 'justificado',
                4 => 'retraso',
                default => 'presente',
            },
            default => match ($session) {
                0, 3, 6 => 'ausente',
                2 => 'justificado',
                1, 5 => 'retraso',
                default => 'presente',
            },
        };
    }

    private function applicationsFor(
        InscripcionAcademica $enrollment,
        Collection $templates,
        Collection $simulations,
        int $index,
    ): Collection {
        $programCode = $enrollment->programa->codigo_prog;
        $pairs = collect();

        if ($programCode === 'PF-UMSA-I-2026') {
            foreach (['MAT', 'FIS', 'QMC'] as $code) {
                foreach ([1, 2] as $partial) {
                    if ($partial === 2 && $index % 4 === 3) {
                        continue;
                    }

                    $template = $templates["{$code}-P{$partial}"];
                    $simulation = $simulations->first(
                        fn (SimulacroProgramado $item) => $item->id_plantilla === $template->id_plan
                            && $item->id_grupo === $enrollment->id_grupo,
                    );
                    $pairs->push([$template, $simulation]);
                }
            }
        } elseif ($programCode === 'PS-UMSA-2026') {
            $pairs->push([$templates['SUF-INT'], null]);
            if ($index % 3 !== 0) {
                $pairs->push([
                    $templates['SUF-SIM'],
                    $simulations->get('Simulacro Integral Ingeniería UMSA'),
                ]);
            }
        } elseif ($programCode === 'NIV-ING-LP-2026') {
            $pairs->push([$templates['DIAG'], $simulations->get('Diagnóstico Inicial')]);
        } else {
            $pairs->push([$templates['MAT-P1'], null]);
            $pairs->push([$templates['FIS-P1'], null]);
        }

        return $pairs;
    }
}
