<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Models\Producto;

final readonly class DatosItemPedido
{
    public function __construct(
        public Producto $producto,
        public int $cantidad,
    ) {}
}
