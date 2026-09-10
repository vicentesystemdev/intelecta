<?php

namespace Database\Seeders;

use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Models\Cargo;
use Illuminate\Database\Seeder;

class CargosSeeder extends Seeder
{
    public array $cargos = [];

    public function run(): void
    {
        $this->cargos = [];
        // Explicit approved starting catalog, never generated from roles.
        foreach ([
            'rectorado' => 'Rector',
            'vicerrectorado' => 'Vicerrector',
            'direccion_carrera' => 'Director de Carrera',
            'coordinacion' => 'Coordinador Académico',
            'docencia' => 'Docente',
            'contabilidad' => 'Contador',
            'recursos_humanos' => 'Responsable de Recursos Humanos',
            'secretaria' => 'Secretaría',
        ] as $key => $name) {
            $this->cargos[$key] = Cargo::firstOrCreate(['nombre_cargo' => $name], ['estado' => EstadoCargo::ACTIVO]);
        }
    }
}
