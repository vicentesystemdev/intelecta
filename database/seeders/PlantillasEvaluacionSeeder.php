<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use Illuminate\Database\Seeder;

class PlantillasEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        // Cargar preguntas ordenadas y agruparlas por área/dificultad para selección coherente
        $preguntas = Pregunta::orderBy('id_preg')->pluck('id_preg')->values();

        // Índices de referencia (0-based sobre el banco ordenado de 60 preguntas):
        // Aritmética [0-9], Álgebra [10-23], Geometría [24-35], Razonamiento [36-47],
        // Estadística [48-53], Resolución de Problemas [54-59]

        $plantillas = [
            // 1. Simulacro PSA – Ingeniería UMSA (10 preguntas × 10 pts = 100)
            [
                'nombre_plan'           => 'Simulacro PSA - Facultad de Ingeniería UMSA',
                'descripcion_plan'      => 'Evaluación integral orientada al ingreso a carreras de ingeniería.',
                'objetivo_plan'         => 'Medir dominio de álgebra, aritmética, geometría y trigonometría.',
                'duracion_minutos_plan' => 90,
                'dificultad_plan'       => 'avanzada',
                'indices'               => [4, 5, 7, 10, 12, 16, 17, 24, 26, 34],
            ],
            // 2. Prueba de Aptitud Académica – Razonamiento Lógico UCB (10 preguntas × 10 pts = 100)
            [
                'nombre_plan'           => 'Prueba de Aptitud Académica - Razonamiento Lógico UCB',
                'descripcion_plan'      => 'Instrumento de aptitud numérica y comprensión lógico-matemática.',
                'objetivo_plan'         => 'Valorar sucesiones, analogías, interpretación y razonamiento cuantitativo.',
                'duracion_minutos_plan' => 75,
                'dificultad_plan'       => 'media-alta',
                'indices'               => [36, 37, 38, 39, 40, 41, 42, 43, 44, 48],
            ],
            // 3. Diagnóstico Matemático – Ciencias Económicas y Financieras (10 × 10 = 100)
            [
                'nombre_plan'           => 'Diagnóstico Matemático - Ciencias Económicas y Financieras',
                'descripcion_plan'      => 'Diagnóstico para postulantes del área económica y financiera.',
                'objetivo_plan'         => 'Identificar fortalezas en porcentajes, proporciones, ecuaciones y estadística.',
                'duracion_minutos_plan' => 70,
                'dificultad_plan'       => 'media',
                'indices'               => [0, 1, 4, 6, 7, 13, 16, 48, 50, 54],
            ],
            // 4. Simulacro Psicotécnico – ESFM / Normal Simón Bolívar (10 × 10 = 100)
            [
                'nombre_plan'           => 'Simulacro Psicotécnico - ESFM / Normal Simón Bolívar',
                'descripcion_plan'      => 'Evaluación de razonamiento para procesos de admisión docente.',
                'objetivo_plan'         => 'Valorar patrones, operadores y comprensión de problemas.',
                'duracion_minutos_plan' => 60,
                'dificultad_plan'       => 'media',
                'indices'               => [36, 38, 39, 41, 43, 44, 50, 55, 57, 58],
            ],
            // 5. Evaluación Mixta de Nivelación – Álgebra y Trigonometría (10 × 10 = 100)
            [
                'nombre_plan'           => 'Evaluación Mixta de Nivelación - Álgebra y Trigonometría',
                'descripcion_plan'      => 'Diagnóstico general para ubicar el nivel inicial del postulante.',
                'objetivo_plan'         => 'Establecer necesidades de nivelación en álgebra y trigonometría.',
                'duracion_minutos_plan' => 80,
                'dificultad_plan'       => 'basica-media',
                'indices'               => [10, 11, 12, 13, 14, 24, 26, 27, 28, 30],
            ],
            // 6. Simulacro de Admisión – Ingeniería EMI (10 × 10 = 100)
            [
                'nombre_plan'           => 'Simulacro de Admisión - Ingeniería EMI',
                'descripcion_plan'      => 'Evaluación orientada al perfil matemático exigido en la EMI.',
                'objetivo_plan'         => 'Medir razonamiento, álgebra y trigonometría en nivel avanzado.',
                'duracion_minutos_plan' => 90,
                'dificultad_plan'       => 'avanzada',
                'indices'               => [5, 9, 15, 20, 21, 22, 25, 31, 32, 45],
            ],
            // 7. Evaluación Diagnóstica – Administración y Economía (10 × 10 = 100)
            [
                'nombre_plan'           => 'Evaluación Diagnóstica - Administración y Economía',
                'descripcion_plan'      => 'Diagnóstico inicial para postulantes de carreras administrativas y económicas.',
                'objetivo_plan'         => 'Evaluar operaciones básicas, porcentajes, estadística y razonamiento contextual.',
                'duracion_minutos_plan' => 65,
                'dificultad_plan'       => 'media',
                'indices'               => [0, 2, 3, 6, 48, 49, 50, 54, 56, 57],
            ],
            // 8. Nivelación Inicial – Razonamiento y Planteo Matemático (10 × 10 = 100)
            [
                'nombre_plan'           => 'Nivelación Inicial - Razonamiento y Planteo Matemático',
                'descripcion_plan'      => 'Prueba de entrada para determinar el nivel base del postulante.',
                'objetivo_plan'         => 'Establecer el punto de partida en razonamiento y planteo de ecuaciones.',
                'duracion_minutos_plan' => 55,
                'dificultad_plan'       => 'basica',
                'indices'               => [0, 1, 2, 3, 36, 37, 38, 39, 54, 55],
            ],
        ];

        foreach ($plantillas as $item) {
            $indices = $item['indices'];
            unset($item['indices']);

            $plantilla = PlantillaEvaluacion::updateOrCreate(
                ['nombre_plan' => $item['nombre_plan']],
                [...$item, 'estado_plan' => 'activa'],
            );

            // Sync: 10 preguntas × puntaje_pp = 10 → suma = 100
            $sync = [];
            foreach ($indices as $orden => $index) {
                if (isset($preguntas[$index])) {
                    $sync[$preguntas[$index]] = ['orden_pp' => $orden + 1, 'puntaje_pp' => 10];
                }
            }
            $plantilla->preguntas()->sync($sync);
        }
    }
}
