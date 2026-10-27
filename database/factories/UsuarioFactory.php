<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
final class UsuarioFactory extends Factory
{
    protected static ?string $password = null;

    protected $model = Usuario::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'es_super_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function sinVerificar(): static
    {
        return $this->state(fn (array $atributos): array => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $atributos): array => [
            'es_super_admin' => true,
        ]);
    }
}
