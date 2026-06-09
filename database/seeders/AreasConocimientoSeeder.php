<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\AreaConocimiento;
use Illuminate\Database\Seeder;

class AreasConocimientoSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['nombre_area' => 'Aritmética', 'descripcion_area' => 'Operaciones numéricas, proporcionalidad, porcentajes y fracciones.'],
            ['nombre_area' => 'Álgebra', 'descripcion_area' => 'Expresiones algebraicas, ecuaciones, sistemas y desigualdades.'],
            ['nombre_area' => 'Geometría y Trigonometría', 'descripcion_area' => 'Figuras, medidas, triángulos y relaciones trigonométricas.'],
            ['nombre_area' => 'Razonamiento Lógico-Matemático', 'descripcion_area' => 'Patrones, operadores, analogías y razonamiento cuantitativo.'],
            ['nombre_area' => 'Estadística y Probabilidades', 'descripcion_area' => 'Análisis descriptivo, conteo e interpretación probabilística.'],
            ['nombre_area' => 'Resolución de Problemas', 'descripcion_area' => 'Modelado matemático de situaciones contextualizadas.'],
        ];

        foreach ($areas as $area) {
            AreaConocimiento::updateOrCreate(
                ['nombre_area' => $area['nombre_area']],
                [...$area, 'estado_area' => 'activo'],
            );
        }
    }
}
