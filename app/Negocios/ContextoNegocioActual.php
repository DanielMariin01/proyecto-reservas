<?php

declare(strict_types=1);

namespace App\Negocios;

use App\Models\Negocio;

/**
 * Único punto de verdad sobre el negocio (tenant) activo en la petición
 * actual. Se fija una sola vez por petición, desde el middleware que
 * resuelve el negocio de la ruta (`ResolverNegocioDesdeRuta` para el
 * panel `app`).
 */
final class ContextoNegocioActual
{
    private ?Negocio $negocio = null;

    public function fijar(?Negocio $negocio): void
    {
        $this->negocio = $negocio;
    }

    public function actual(): ?Negocio
    {
        return $this->negocio;
    }

    public function limpiar(): void
    {
        $this->negocio = null;
    }
}
