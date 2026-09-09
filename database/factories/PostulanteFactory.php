<?php

namespace Database\Factories;

use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Postulante> */
class PostulanteFactory extends Factory
{
    protected $model = Postulante::class;

    public function definition(): array
    {
        return [
            'nombres_post' => 'Persona',
            'apellidos_post' => 'Prueba Académica',
            'email_post' => fake()->unique()->safeEmail(),
            'gestion_post' => 2026,
            'estado_post' => 'activo',
            'user_id' => null,
        ];
    }

    public function withUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
