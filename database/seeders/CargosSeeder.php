<?php

namespace Database\Seeders;

use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Models\Cargo;
use App\Support\Validation\InputNormalizer;
use Illuminate\Database\Seeder;

class CargosSeeder extends Seeder
{
    public array $cargos = [];

    public function run(): void
    {
        $this->cargos = [];
        // Explicit approved starting catalog, never generated from roles.
        foreach ([
            'rectorado' => ['Rector', 'Máxima autoridad ejecutiva y representante institucional.'],
            'vicerrectorado' => ['Vicerrector', 'Autoridad responsable de apoyar la dirección institucional.'],
            'direccion_carrera' => ['Director de Carrera', 'Responsable de la conducción académica de una carrera.'],
            'coordinacion' => ['Coordinador Académico', 'Responsable de coordinar programas, grupos y seguimiento académico.'],
            'docencia' => ['Docente', 'Personal responsable de la enseñanza y acompañamiento académico.'],
            'contabilidad' => ['Contador', 'Responsable del registro y control contable institucional.'],
            'recursos_humanos' => ['Responsable de Recursos Humanos', 'Responsable de la administración institucional del personal.'],
            'secretaria' => ['Secretaría', 'Responsable del apoyo administrativo y la atención institucional.'],
        ] as $key => [$name, $description]) {
            $cargo = Cargo::query()->get()->first(
                fn (Cargo $cargo) => InputNormalizer::key($cargo->nombre_cargo) === InputNormalizer::key($name),
            );

            if (! $cargo) {
                $cargo = new Cargo([
                    'nombre_cargo' => $name,
                    'descripcion' => $description,
                ]);
                $cargo->estado = EstadoCargo::ACTIVO;
                $cargo->save();
            }

            $this->cargos[$key] = $cargo;
        }
    }
}
