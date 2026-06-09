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
            // UMSA – 7 carreras
            ['universidad' => 'UMSA', 'nombre_car' => 'Ingeniería de Sistemas',      'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Ingeniería Civil',              'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Ingeniería Industrial',         'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Economía',                      'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Administración de Empresas',    'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Derecho',                       'area_car' => 'Humanidades',  'nivel_exigencia_matematica_car' => 'Baja-media'],
            ['universidad' => 'UMSA', 'nombre_car' => 'Psicología',                    'area_car' => 'Humanidades',  'nivel_exigencia_matematica_car' => 'Media'],

            // EMI – 4 carreras
            ['universidad' => 'EMI',  'nombre_car' => 'Ingeniería de Sistemas',        'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'EMI',  'nombre_car' => 'Ingeniería Mecatrónica',         'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Alta'],
            ['universidad' => 'EMI',  'nombre_car' => 'Ingeniería Comercial',           'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'EMI',  'nombre_car' => 'Ingeniería Civil',               'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Alta'],

            // UCB – 4 carreras
            ['universidad' => 'UCB',  'nombre_car' => 'Economía',                      'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UCB',  'nombre_car' => 'Administración de Empresas',    'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UCB',  'nombre_car' => 'Ingeniería Comercial',           'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media'],
            ['universidad' => 'UCB',  'nombre_car' => 'Psicología',                    'area_car' => 'Humanidades',  'nivel_exigencia_matematica_car' => 'Media'],

            // UNIFRANZ – 3 carreras
            ['universidad' => 'UNIFRANZ', 'nombre_car' => 'Ingeniería de Sistemas',    'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Media-alta'],
            ['universidad' => 'UNIFRANZ', 'nombre_car' => 'Administración de Empresas','area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media'],
            ['universidad' => 'UNIFRANZ', 'nombre_car' => 'Medicina',                  'area_car' => 'Ciencias',     'nivel_exigencia_matematica_car' => 'Media-alta'],

            // UPEA – 3 carreras
            ['universidad' => 'UPEA', 'nombre_car' => 'Ciencias de la Educación',      'area_car' => 'Humanidades',  'nivel_exigencia_matematica_car' => 'Media'],
            ['universidad' => 'UPEA', 'nombre_car' => 'Ingeniería de Sistemas',         'area_car' => 'Ingeniería',   'nivel_exigencia_matematica_car' => 'Media'],
            ['universidad' => 'UPEA', 'nombre_car' => 'Contaduría Pública',             'area_car' => 'Empresarial',  'nivel_exigencia_matematica_car' => 'Media-alta'],
        ];

        foreach ($carreras as $carrera) {
            $sigla = $carrera['universidad'];
            unset($carrera['universidad']);

            Carrera::updateOrCreate(
                [
                    'id_uni'     => $universidades[$sigla],
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
