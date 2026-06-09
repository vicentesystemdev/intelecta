<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use Illuminate\Database\Seeder;

class AreasConocimientoSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['materia' => 'MAT', 'nombre_area' => 'Aritmética', 'descripcion_area' => 'Operaciones numéricas, proporcionalidad, porcentajes y fracciones.'],
            ['materia' => 'MAT', 'nombre_area' => 'Álgebra', 'descripcion_area' => 'Expresiones algebraicas, ecuaciones, sistemas y desigualdades.'],
            ['materia' => 'MAT', 'nombre_area' => 'Geometría y Trigonometría', 'descripcion_area' => 'Figuras, medidas, triángulos y relaciones trigonométricas.'],
            ['materia' => 'PAA', 'nombre_area' => 'Razonamiento Lógico-Matemático', 'descripcion_area' => 'Patrones, operadores, analogías y razonamiento cuantitativo.'],
            ['materia' => 'MAT', 'nombre_area' => 'Estadística y Probabilidades', 'descripcion_area' => 'Análisis descriptivo, conteo e interpretación probabilística.'],
            ['materia' => 'PAA', 'nombre_area' => 'Resolución de Problemas', 'descripcion_area' => 'Modelado matemático de situaciones contextualizadas.'],
            ['materia' => 'FIS', 'nombre_area' => 'Física Preuniversitaria', 'descripcion_area' => 'Magnitudes, movimiento, fuerzas, energía y electricidad para el ingreso a Ingeniería.'],
            ['materia' => 'QMC', 'nombre_area' => 'Química Preuniversitaria', 'descripcion_area' => 'Materia, estructura atómica, nomenclatura, reacciones y estequiometría.'],
        ];

        foreach ($areas as $area) {
            $materia = Materia::where('codigo_mat', $area['materia'])->firstOrFail();
            unset($area['materia']);

            AreaConocimiento::updateOrCreate(
                ['nombre_area' => $area['nombre_area']],
                [...$area, 'id_mat' => $materia->id_mat, 'estado_area' => 'activo'],
            );
        }
    }
}
