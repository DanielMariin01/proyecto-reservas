<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * El valor que se guarda en la columna `citas.estado`. La lógica de qué
 * transición es válida vive en las clases de App\Estados\Cita (patrón
 * Estado), no aquí: este enum es solo el dato persistido.
 */
enum EstadoCitaValor: string implements HasColor, HasLabel
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case EnAtencion = 'en_atencion';
    case Completada = 'completada';
    case Cancelada = 'cancelada';
    case NoAsistio = 'no_asistio';
    case Reprogramada = 'reprogramada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmada => 'Confirmada',
            self::EnAtencion => 'En atención',
            self::Completada => 'Completada',
            self::Cancelada => 'Cancelada',
            self::NoAsistio => 'No asistió',
            self::Reprogramada => 'Reprogramada',
        };
    }

    public function getLabel(): string
    {
        return $this->etiqueta();
    }

    /**
     * Colores de la skill diseno-reservas (componente Insignia). La skill
     * solo define confirmada/pendiente/cancelada/no asistió; en atención y
     * completada reusan --primario y --secundario respectivamente, y
     * reprogramada no se muestra nunca (pasa a confirmada en el acto).
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Confirmada, self::Completada => 'success',
            self::EnAtencion => 'primary',
            self::Cancelada, self::NoAsistio => 'gray',
            self::Reprogramada => 'gray',
        };
    }
}
