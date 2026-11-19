<?php

declare(strict_types=1);

namespace App\Events;

use App\DatosTransferencia\DatosAuditoria;
use App\Models\Cliente;
use App\Models\Negocio;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * RF-CLI-02: un cliente se identifica por celular al reservar, sin
 * importar el nombre que escriba (ver ResolverOCrearCliente) - es a
 * propósito, para no duplicar clientes por una errata de tecleo. Pero si
 * el nombre no coincide con el registrado, vale la pena dejarlo en
 * auditoría: puede ser la misma persona con otro nombre, o un celular que
 * ahora usa alguien distinto, y el dueño debería poder notarlo.
 */
final class ClienteConNombreDistintoEnReserva implements EventoAuditable
{
    use Dispatchable;

    public function __construct(
        public readonly Cliente $cliente,
        public readonly string $nombreEnLaReserva,
    ) {}

    public function datosAuditoria(): DatosAuditoria
    {
        return new DatosAuditoria(
            accion: 'cliente.nombre_distinto_en_reserva',
            negocio: Negocio::find($this->cliente->negocio_id),
            modelo: Cliente::class,
            modeloId: $this->cliente->id,
            valoresAntes: ['nombre_registrado' => $this->cliente->nombre],
            valoresDespues: ['nombre_en_la_reserva' => $this->nombreEnLaReserva],
        );
    }
}
