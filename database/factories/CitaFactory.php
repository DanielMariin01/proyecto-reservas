<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoCitaValor;
use App\Enums\OrigenCita;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
final class CitaFactory extends Factory
{
    protected $model = Cita::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = now()->addDay()->setTime(10, 0);

        return [
            'negocio_id' => Negocio::factory(),
            'profesional_id' => Profesional::factory(),
            'servicio_id' => Servicio::factory(),
            'cliente_id' => Cliente::factory(),
            'inicio' => $inicio,
            'fin' => $inicio->copy()->addMinutes(45),
            'estado' => EstadoCitaValor::Confirmada,
            'precio' => 20000,
            'origen' => OrigenCita::Panel,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $atributos): array => ['estado' => EstadoCitaValor::Pendiente]);
    }

    public function cancelada(): static
    {
        return $this->state(fn (array $atributos): array => ['estado' => EstadoCitaValor::Cancelada]);
    }
}
