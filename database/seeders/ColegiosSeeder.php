<?php

namespace Database\Seeders;

use App\Domains\Institucional\Models\Colegio;
use Illuminate\Database\Seeder;

class ColegiosSeeder extends Seeder
{
    public function run(): void
    {
        $colegios = [
            ['nombre_col' => 'Colegio Nacional San Simón', 'tipo_col' => 'Público', 'ubicacion_col' => 'Cochabamba', 'estado_col' => 'activo'],
            ['nombre_col' => 'Unidad Educativa Bolívar', 'tipo_col' => 'Público', 'ubicacion_col' => 'Cochabamba', 'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio Alemán Santa María', 'tipo_col' => 'Privado', 'ubicacion_col' => 'Cochabamba', 'estado_col' => 'activo'],
            ['nombre_col' => 'Unidad Educativa Técnico Humanístico', 'tipo_col' => 'Público', 'ubicacion_col' => 'Quillacollo', 'estado_col' => 'activo'],
            ['nombre_col' => 'Instituto Americano', 'tipo_col' => 'Privado', 'ubicacion_col' => 'Cochabamba', 'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio Don Bosco', 'tipo_col' => 'Convenio', 'ubicacion_col' => 'Cochabamba', 'estado_col' => 'activo'],
        ];

        foreach ($colegios as $colegio) {
            Colegio::updateOrCreate(
                ['nombre_col' => $colegio['nombre_col']],
                $colegio
            );
        }
    }
}
