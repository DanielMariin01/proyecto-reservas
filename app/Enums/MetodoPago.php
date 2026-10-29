<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * RF-PAG-01: los métodos de pago manual que acepta el negocio.
 */
enum MetodoPago: string implements HasLabel
{
    case Efectivo = 'efectivo';
    case Nequi = 'nequi';
    case Daviplata = 'daviplata';
    case Transferencia = 'transferencia';
    case Tarjeta = 'tarjeta';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Nequi => 'Nequi',
            self::Daviplata => 'Daviplata',
            self::Transferencia => 'Transferencia',
            self::Tarjeta => 'Tarjeta (datáfono)',
        };
    }

    public function getLabel(): string
    {
        return $this->etiqueta();
    }
}
