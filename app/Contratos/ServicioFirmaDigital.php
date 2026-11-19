<?php

declare(strict_types=1);

namespace App\Contratos;

/**
 * Adaptador sobre el almacenamiento de las firmas dibujadas en el `PadFirma`
 * (CLAUDE.md: "servicios externos siempre detrás de una interfaz propia").
 * Las acciones de historia clínica nunca llaman a `Storage` directamente:
 * dependen de esta interfaz, lo que permite cambiar de disco local a otro
 * proveedor sin tocarlas. RF-HCL-04: el disco siempre es privado.
 */
interface ServicioFirmaDigital
{
    /**
     * Guarda una firma recibida como "data URL" base64 (formato que entrega
     * el canvas del `PadFirma`) y devuelve la ruta en el disco privado.
     */
    public function guardar(string $imagenBase64, string $carpeta): string;

    public function eliminar(?string $ruta): void;
}
