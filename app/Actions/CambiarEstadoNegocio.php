<?php

declare(strict_types=1);

namespace App\Actions;

use App\DatosTransferencia\DatosCambioEstadoNegocio;
use App\Enums\EstadoSuscripcion;
use App\Events\EstadoNegocioCambiado;
use Carbon\Carbon;

final class CambiarEstadoNegocio
{
    public function ejecutar(DatosCambioEstadoNegocio $datos): void
    {
        $negocio = $datos->negocio;
        $estadoAnterior = $negocio->estado_suscripcion;

        if ($estadoAnterior === $datos->estadoDestino) {
            return;
        }

        $negocio->estado_suscripcion = $datos->estadoDestino;
        $negocio->vencido_desde = $this->calcularMarcaDeTiempo($datos->estadoDestino, EstadoSuscripcion::Vencido, $negocio->vencido_desde);
        $negocio->suspendido_desde = $this->calcularMarcaDeTiempo($datos->estadoDestino, EstadoSuscripcion::Suspendido, $negocio->suspendido_desde);
        $negocio->cancelado_desde = $this->calcularMarcaDeTiempo($datos->estadoDestino, EstadoSuscripcion::Cancelado, $negocio->cancelado_desde);
        $negocio->save();

        EstadoNegocioCambiado::dispatch(
            $negocio,
            $estadoAnterior,
            $datos->estadoDestino,
            $datos->motivo,
            $datos->cambiadoPor,
        );
    }

    private function calcularMarcaDeTiempo(
        EstadoSuscripcion $estadoDestino,
        EstadoSuscripcion $estadoDeLaMarca,
        ?Carbon $valorActual,
    ): ?Carbon {
        if ($estadoDestino === $estadoDeLaMarca) {
            return now();
        }

        if ($estadoDestino === EstadoSuscripcion::Activo) {
            return null;
        }

        return $valorActual;
    }
}
