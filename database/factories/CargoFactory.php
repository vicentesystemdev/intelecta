<?php

namespace Database\Factories;

use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Models\Cargo;
use Illuminate\Database\Eloquent\Factories\Factory;

class CargoFactory extends Factory
{
    protected $model = Cargo::class;

    public function definition(): array
    {
        return ['nombre_cargo' => 'Cargo de prueba '.fake()->unique()->numerify('########'), 'descripcion' => null, 'estado' => EstadoCargo::ACTIVO];
    }

    public function active(): static
    {
        return $this->state(['estado' => EstadoCargo::ACTIVO]);
    }

    public function inactive(): static
    {
        return $this->state(['estado' => EstadoCargo::INACTIVO]);
    }
}
