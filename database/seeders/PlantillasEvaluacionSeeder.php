<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use Illuminate\Database\Seeder;

class PlantillasEvaluacionSeeder extends Seeder
{
    public function run(): void
    {
        $preguntas = Pregunta::orderBy('id_preg')->pluck('id_preg')->values();

        $plantillas = [
            [
                'nombre_plan' => 'Simulacro PSA - Facultad de Ingeniería UMSA',
                'descripcion_plan' => 'Evaluación integral orientada al ingreso a carreras de ingeniería.',
                'objetivo_plan' => 'Medir dominio de álgebra, aritmética, geometría y trigonometría.',
                'duracion_minutos_plan' => 90,
                'dificultad_plan' => 'avanzada',
                'indices' => [0, 1, 2, 5, 6, 7, 8, 10, 11, 12],
            ],
            [
                'nombre_plan' => 'Prueba de Aptitud Académica - Razonamiento Lógico UCB',
                'descripcion_plan' => 'Instrumento de aptitud numérica y comprensión lógico-matemática.',
                'objetivo_plan' => 'Valorar sucesiones, analogías, interpretación y razonamiento cuantitativo.',
                'duracion_minutos_plan' => 75,
                'dificultad_plan' => 'media-alta',
                'indices' => [15, 16, 17, 18, 19, 20, 21, 24, 28, 29],
            ],
            [
                'nombre_plan' => 'Diagnóstico Matemático - Ciencias Económicas y Financieras',
                'descripcion_plan' => 'Diagnóstico para postulantes del área económica y financiera.',
                'objetivo_plan' => 'Identificar fortalezas en porcentajes, proporciones, ecuaciones y estadística.',
                'duracion_minutos_plan' => 70,
                'dificultad_plan' => 'media',
                'indices' => [0, 1, 3, 4, 7, 8, 20, 21, 23, 24],
            ],
            [
                'nombre_plan' => 'Simulacro Psicotécnico - ESFM / Normal Simón Bolívar',
                'descripcion_plan' => 'Evaluación de razonamiento para procesos de admisión docente.',
                'objetivo_plan' => 'Valorar patrones, operadores y comprensión de problemas.',
                'duracion_minutos_plan' => 60,
                'dificultad_plan' => 'media',
                'indices' => [15, 16, 17, 18, 19, 21, 24, 26, 28, 29],
            ],
            [
                'nombre_plan' => 'Evaluación Mixta de Nivelación - Álgebra y Trigonometría',
                'descripcion_plan' => 'Diagnóstico general para ubicar el nivel inicial del postulante.',
                'objetivo_plan' => 'Establecer necesidades de nivelación en álgebra y trigonometría.',
                'duracion_minutos_plan' => 80,
                'dificultad_plan' => 'basica-media',
                'indices' => [5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
            ],
        ];

        foreach ($plantillas as $item) {
            $indices = $item['indices'];
            unset($item['indices']);

            $plantilla = PlantillaEvaluacion::updateOrCreate(
                ['nombre_plan' => $item['nombre_plan']],
                [...$item, 'estado_plan' => 'activa'],
            );

            $sync = [];
            foreach ($indices as $orden => $index) {
                $sync[$preguntas[$index]] = ['orden_pp' => $orden + 1, 'puntaje_pp' => 10];
            }
            $plantilla->preguntas()->sync($sync);
        }
    }
}
