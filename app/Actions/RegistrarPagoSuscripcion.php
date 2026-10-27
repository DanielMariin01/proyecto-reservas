<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosCambioEstadoNegocio;
use App\DatosTransferencia\DatosPagoSuscripcion;
use App\Enums\EstadoSuscripcion;
use App\Events\PagoSuscripcionRegistrado;
use App\Models\PagoSuscripcion;

final class RegistrarPagoSuscripcion
{
    public function __construct(
        private readonly CambiarEstadoNegocio $cambiarEstadoNegocio,
    ) {}

    public function ejecutar(DatosPagoSuscripcion $datos): PagoSuscripcion
    {
        $pago = PagoSuscripcion::create([
            'negocio_id' => $datos->negocio->id,
            'fecha' => $datos->fecha,
            'valor' => $datos->valor,
            'metodo' => $datos->metodo,
            'periodo_desde' => $datos->periodoDesde,
            'periodo_hasta' => $datos->periodoHasta,
            'registrado_por_id' => $datos->registradoPor?->id,
        ]);

        $datos->negocio->proxima_fecha_pago = $datos->periodoHasta;
        $datos->negocio->save();

        if ($datos->negocio->estado_suscripcion !== EstadoSuscripcion::Activo) {
            $this->cambiarEstadoNegocio->ejecutar(new DatosCambioEstadoNegocio(
                negocio: $datos->negocio,
                estadoDestino: EstadoSuscripcion::Activo,
                motivo: 'Reactivado automáticamente al registrar un pago de suscripción.',
                cambiadoPor: $datos->registradoPor,
            ));
        }

        PagoSuscripcionRegistrado::dispatch($datos->negocio, $pago, $datos->registradoPor);

        return $pago;
    }
}
