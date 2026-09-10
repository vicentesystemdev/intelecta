<?php

namespace Database\Factories;

use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\PersonalInstitucional;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalInstitucionalFactory extends Factory
{
    protected $model = PersonalInstitucional::class;

    public function definition(): array
    {
        return ['user_id' => null, 'cargo_id' => null, 'nombres' => 'María Elena', 'apellidos' => 'Quispe Rojas', 'ci' => null, 'celular' => null, 'correo_contacto' => null, 'estado' => EstadoPersonal::PENDIENTE];
    }

    public function active(): static
    {
        return $this->state(['estado' => EstadoPersonal::ACTIVO]);
    }

    public function inactive(): static
    {
        return $this->state(['estado' => EstadoPersonal::INACTIVO]);
    }

    public function pending(): static
    {
        return $this->state(['estado' => EstadoPersonal::PENDIENTE]);
    }
}
