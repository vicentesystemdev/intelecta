<?php

namespace Database\Seeders;

use App\Domains\Institucional\Models\Colegio;
use Illuminate\Database\Seeder;

class ColegiosSeeder extends Seeder
{
    public function run(): void
    {
        $colegios = [
            ['nombre_col' => 'Unidad Educativa Integración Andina',   'tipo_col' => 'Privado',  'ubicacion_col' => 'La Paz',   'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio Don Bosco',                      'tipo_col' => 'Convenio', 'ubicacion_col' => 'La Paz',   'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio La Salle',                       'tipo_col' => 'Privado',  'ubicacion_col' => 'La Paz',   'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio Nacional Ayacucho',              'tipo_col' => 'Público',  'ubicacion_col' => 'La Paz',   'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio Bolívar',                        'tipo_col' => 'Público',  'ubicacion_col' => 'La Paz',   'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio San Calixto',                    'tipo_col' => 'Privado',  'ubicacion_col' => 'La Paz',   'estado_col' => 'activo'],
            ['nombre_col' => 'Colegio Técnico Humanístico El Alto',    'tipo_col' => 'Público',  'ubicacion_col' => 'El Alto',  'estado_col' => 'activo'],
            ['nombre_col' => 'Unidad Educativa República de Venezuela', 'tipo_col' => 'Público', 'ubicacion_col' => 'El Alto',  'estado_col' => 'activo'],
        ];

        foreach ($colegios as $colegio) {
            Colegio::updateOrCreate(
                ['nombre_col' => $colegio['nombre_col']],
                $colegio
            );
        }
    }
}
