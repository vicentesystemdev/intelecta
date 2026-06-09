<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Database\Seeder;

class TemasSeeder extends Seeder
{
    public function run(): void
    {
        $temas = [
            'Aritmética' => [
                'Operaciones básicas', 'Razones y proporciones', 'Regla de tres simple y compuesta',
                'Porcentajes', 'Fracciones', 'Problemas financieros básicos',
            ],
            'Álgebra' => [
                'Factorización', 'Productos notables', 'Ecuaciones lineales', 'Ecuaciones de segundo grado',
                'Sistemas de ecuaciones', 'Logaritmos', 'Desigualdades básicas',
            ],
            'Geometría y Trigonometría' => [
                'Áreas de figuras planas', 'Perímetros', 'Teorema de Pitágoras', 'Razones trigonométricas',
                'Identidades trigonométricas', 'Triángulos rectángulos', 'Triángulos oblicuángulos',
            ],
            'Razonamiento Lógico-Matemático' => [
                'Sucesiones numéricas', 'Sucesiones alfanuméricas', 'Operadores no convencionales',
                'Problemas de edades', 'Planteo de ecuaciones', 'Analogías lógico-matemáticas',
            ],
            'Estadística y Probabilidades' => [
                'Promedio aritmético', 'Mediana y moda', 'Probabilidad básica',
                'Conteo simple', 'Interpretación de tablas',
            ],
            'Resolución de Problemas' => [
                'Problemas contextualizados', 'Interpretación de enunciados',
                'Modelado con ecuaciones', 'Comparación de magnitudes',
            ],
        ];

        foreach ($temas as $nombreArea => $nombres) {
            $area = AreaConocimiento::where('nombre_area', $nombreArea)->firstOrFail();

            foreach ($nombres as $index => $nombreTema) {
                Tema::updateOrCreate(
                    ['id_area' => $area->id_area, 'nombre_tem' => $nombreTema],
                    [
                        'descripcion_tem' => "Contenidos preuniversitarios de {$nombreTema}.",
                        'nivel_tem' => $index < 2 ? 'basico' : ($index < 5 ? 'intermedio' : 'avanzado'),
                        'estado_tem' => 'activo',
                    ],
                );
            }
        }
    }
}
