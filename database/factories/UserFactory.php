<?php

namespace Database\Factories;

use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            // Explicit default for existing authenticated-module fixtures.
            'estado_cuenta' => EstadoCuenta::ACTIVA,
            'version_acceso' => 0,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->pending();
    }

    public function active(): static
    {
        return $this->state(fn () => ['estado_cuenta' => EstadoCuenta::ACTIVA, 'email_verified_at' => now()]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado_cuenta' => EstadoCuenta::PENDIENTE,
            'email_verified_at' => null,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['estado_cuenta' => EstadoCuenta::BLOQUEADA]);
    }
}
