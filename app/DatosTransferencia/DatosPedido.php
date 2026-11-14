<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

use App\Enums\TipoEntregaValor;
use App\Models\Negocio;

final readonly class DatosPedido
{
    /**
     * @param  list<DatosItemPedido>  $items
     */
    public function __construct(
        public Negocio $negocio,
        public array $items,
        public string $nombreCliente,
        public string $celularCliente,
        public ?string $correoCliente,
        public TipoEntregaValor $tipoEntrega,
        public ?string $barrio = null,
        public ?string $nota = null,
    ) {}
}
