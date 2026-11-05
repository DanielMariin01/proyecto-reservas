<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ServicioSaludDelSistema;
use Illuminate\Http\JsonResponse;

/**
 * RNF-OBS-05: ruta pública (sin autenticación) que un monitor externo
 * gratuito vigila para avisar si el sitio se cae.
 */
final class EstadoController
{
    public function __construct(private readonly ServicioSaludDelSistema $servicio) {}

    public function mostrar(): JsonResponse
    {
        $resultado = $this->servicio->verificar();

        return response()->json([
            'estado' => $resultado->saludable() ? 'ok' : 'fallo',
            'verificaciones' => $resultado->comoArreglo(),
        ], $resultado->saludable() ? 200 : 503);
    }
}
