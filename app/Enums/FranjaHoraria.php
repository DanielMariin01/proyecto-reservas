<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * "Huecos libres de hoy" (sección 7 de la skill de diseño, item 9 del QA
 * visual) se agrupa en estas 3 franjas con un chip cada una, en vez de
 * una sola lista larga de horas.
 */
enum FranjaHoraria: string
{
    case Manana = 'manana';
    case Tarde = 'tarde';
    case Noche = 'noche';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Manana => 'Mañana',
            self::Tarde => 'Tarde',
            self::Noche => 'Noche',
        };
    }

    private function horaInicio(): int
    {
        return match ($this) {
            self::Manana => 6,
            self::Tarde => 12,
            self::Noche => 18,
        };
    }

    private function horaFin(): int
    {
        return match ($this) {
            self::Manana => 12,
            self::Tarde => 18,
            self::Noche => 22,
        };
    }

    /**
     * El rango de esta franja en UTC, para el día local que representa
     * $fechaLocal (ya en la zona horaria del negocio, a las 00:00).
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function rangoUtc(CarbonImmutable $fechaLocal): array
    {
        return [
            $fechaLocal->setTime($this->horaInicio(), 0)->utc(),
            $fechaLocal->setTime($this->horaFin(), 0)->utc(),
        ];
    }

    /**
     * La franja a la que pertenece $momento, según la hora local del
     * negocio (ej. una reserva a las 11:30 a. m. cae en Mañana, aunque
     * Mañana termine a las 12:00). Una hora fuera de las 3 franjas (antes
     * de las 6 a. m.) cae en Noche, la más cercana por el otro extremo.
     */
    public static function paraHora(CarbonInterface $momento, string $zonaHoraria): self
    {
        $horaLocal = $momento->setTimezone($zonaHoraria)->hour;

        foreach (self::cases() as $franja) {
            if ($horaLocal >= $franja->horaInicio() && $horaLocal < $franja->horaFin()) {
                return $franja;
            }
        }

        return self::Noche;
    }
}
