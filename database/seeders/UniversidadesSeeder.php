<?php

namespace Database\Seeders;

use App\Domains\Institucional\Models\Universidad;
use Illuminate\Database\Seeder;

class UniversidadesSeeder extends Seeder
{
    public function run(): void
    {
        $universidades = [
            ['nombre_uni' => 'Universidad Mayor de San Andrés', 'sigla_uni' => 'UMSA', 'tipo_uni' => 'Pública', 'departamento_uni' => 'La Paz', 'nivel_exigencia_matematica_uni' => 'Alta', 'estado_uni' => 'activo'],
            ['nombre_uni' => 'Escuela Militar de Ingeniería', 'sigla_uni' => 'EMI', 'tipo_uni' => 'Privada', 'departamento_uni' => 'La Paz', 'nivel_exigencia_matematica_uni' => 'Alta', 'estado_uni' => 'activo'],
            ['nombre_uni' => 'Universidad Privada Franz Tamayo', 'sigla_uni' => 'UNIFRANZ', 'tipo_uni' => 'Privada', 'departamento_uni' => 'La Paz', 'nivel_exigencia_matematica_uni' => 'Media-alta', 'estado_uni' => 'activo'],
            ['nombre_uni' => 'Universidad Católica Boliviana San Pablo', 'sigla_uni' => 'UCB', 'tipo_uni' => 'Privada', 'departamento_uni' => 'La Paz', 'nivel_exigencia_matematica_uni' => 'Media-alta', 'estado_uni' => 'activo'],
            ['nombre_uni' => 'Universidad Pública de El Alto', 'sigla_uni' => 'UPEA', 'tipo_uni' => 'Pública', 'departamento_uni' => 'El Alto', 'nivel_exigencia_matematica_uni' => 'Media', 'estado_uni' => 'activo'],
        ];

        foreach ($universidades as $universidad) {
            Universidad::updateOrCreate(
                ['sigla_uni' => $universidad['sigla_uni']],
                $universidad
            );
        }
    }
}
