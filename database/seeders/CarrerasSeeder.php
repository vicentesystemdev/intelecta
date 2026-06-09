<?php

namespace Database\Seeders;

use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Universidad;
use Illuminate\Database\Seeder;

class CarrerasSeeder extends Seeder
{
    public function run(): void
    {
        $universidades = Universidad::pluck('id_uni', 'sigla_uni');

        $carreras = [
            ['universidad' => 'UMSA', 'nombre_car' => 'Ingeniería Civil', 'area_car' => 'Ingeniería', 'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Ingeniería de Sistemas', 'area_car' => 'Ingeniería', 'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Psicología', 'area_car' => 'Humanidades', 'nivel_exigencia_matematica_car' => 'Media'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Derecho', 'area_car' => 'Humanidades', 'nivel_exigencia_matematica_car' => 'Baja-media'],
            ['universidad' => 'EMI', 'nombre_car' => 'Ingeniería de Sistemas', 'area_car' => 'Ingeniería', 'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'EMI', 'nombre_car' => 'Ingeniería Comercial', 'area_car' => 'Empresarial', 'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UNIFRANZ', 'nombre_car' => 'Ingeniería de Sistemas', 'area_car' => 'Ingeniería', 'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UCB', 'nombre_car' => 'Economía', 'area_car' => 'Empresarial', 'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UPEA', 'nombre_car' => 'Ciencias de la Educación', 'area_car' => 'Humanidades', 'nivel_exigencia_matematica_car' => 'Media'],
        ];

        foreach ($carreras as $carrera) {
            $sigla = $carrera['universidad'];
            unset($carrera['universidad']);

            Carrera::updateOrCreate(
                [
                    'id_uni' => $universidades[$sigla],
                    'nombre_car' => $carrera['nombre_car'],
                ],
                [
                    ...$carrera,
                    'estado_car' => 'activo',
                ]
            );
        }
    }
}
