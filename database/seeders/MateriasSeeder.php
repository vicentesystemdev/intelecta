<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\Materia;
use Illuminate\Database\Seeder;

class MateriasSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            [
                'codigo_mat' => 'MAT',
                'nombre_mat' => 'Matemática',
                'descripcion_mat' => 'Fundamentos matemáticos y resolución cuantitativa para la preparación preuniversitaria.',
                'color_mat' => 'indigo',
                'icono_mat' => 'Calculator',
            ],
            [
                'codigo_mat' => 'FIS',
                'nombre_mat' => 'Física',
                'descripcion_mat' => 'Comprensión de fenómenos físicos, magnitudes y modelos aplicados a Ingeniería.',
                'color_mat' => 'sky',
                'icono_mat' => 'Atom',
            ],
            [
                'codigo_mat' => 'QMC',
                'nombre_mat' => 'Química',
                'descripcion_mat' => 'Principios de materia, estructura atómica, reacciones y cálculo químico.',
                'color_mat' => 'cyan',
                'icono_mat' => 'FlaskConical',
            ],
            [
                'codigo_mat' => 'PAA',
                'nombre_mat' => 'Razonamiento Académico / Aptitud Matemática',
                'descripcion_mat' => 'Aptitud cuantitativa, razonamiento lógico y resolución estratégica de problemas.',
                'color_mat' => 'violet',
                'icono_mat' => 'BrainCircuit',
            ],
        ];

        foreach ($materias as $materia) {
            Materia::updateOrCreate(
                ['codigo_mat' => $materia['codigo_mat']],
                [...$materia, 'estado_mat' => 'activo'],
            );
        }
    }
}
