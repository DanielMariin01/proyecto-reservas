<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AutorizacionFotos;
use App\Enums\CondicionPiel;
use App\Enums\CondicionUna;
use App\Enums\DecisionValoracion;
use App\Enums\FormaUna;
use App\Enums\LargoUna;
use App\Enums\ServicioDiseno;
use App\Models\Cliente;
use App\Models\HistoriaClinica;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistoriaClinica>
 */
final class HistoriaClinicaFactory extends Factory
{
    protected $model = HistoriaClinica::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negocio_id' => Negocio::factory(),
            'cliente_id' => Cliente::factory(),
            'profesional_id' => Profesional::factory(),
            'usuario_id' => Usuario::factory(),
            'fecha' => now()->toDateString(),
            'piel' => [CondicionPiel::Normal->value],
            'una' => [CondicionUna::SinAlteraciones->value],
            'hallazgos_visibles' => [],
            'decision' => DecisionValoracion::SeRealiza,
            'servicio_diseno' => ServicioDiseno::Semipermanente,
            'forma_una' => FormaUna::Almendrada,
            'largo_una' => LargoUna::Medio,
            'hora_inicio' => '10:00',
            'hora_final' => '11:00',
            'valor' => 40000,
            'abono' => 0,
            'saldo' => 40000,
            'autorizacion_fotos' => AutorizacionFotos::SoloHistorialPrivado,
        ];
    }
}
