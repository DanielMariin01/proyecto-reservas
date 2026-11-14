<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * El valor que se guarda en la columna `pedidos.estado`. La lógica de qué
 * transición es válida vive en las clases de App\Estados\Pedido (patrón
 * Estado), no aquí: este enum es solo el dato persistido.
 */
enum EstadoPedidoValor: string
{
    case Pendiente = 'pendiente';
    case Pagado = 'pagado';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Pagado => 'Pagado',
            self::Entregado => 'Entregado',
            self::Cancelado => 'Cancelado',
        };
    }
}
