<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonImmutable;

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
}
