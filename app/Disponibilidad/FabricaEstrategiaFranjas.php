<?php

declare(strict_types=1);

namespace App\Disponibilidad;

use App\Models\Negocio;
use App\Models\Servicio;

/**
 * Elige la estrategia de generación de franjas según la configuración del
 * negocio/servicio (sección 5: "intervalo de franjas, por defecto igual a
 * la duración del servicio").
 */
final class FabricaEstrategiaFranjas
{
    public function paraServicio(Servicio $servicio): EstrategiaGeneracionFranjas
    {
        $negocio = Negocio::findOrFail($servicio->negocio_id);
        $intervalo = $servicio->intervalo_franjas_minutos ?? $negocio->intervalo_franjas_minutos;

        if ($intervalo !== null) {
            return new EstrategiaFranjaFlexible($intervalo);
        }

        return new EstrategiaFranjaPorDuracion;
    }
}
