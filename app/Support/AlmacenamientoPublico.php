<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class AlmacenamientoPublico
{
    /**
     * `foto`/`imagen` deben guardar la ruta relativa del disco `public`
     * (ej. "servicios/corte.jpg"), nunca la URL completa: los Resources
     * ya la construyen con Storage::url() al leer, y si ya viene como
     * URL completa (por ejemplo porque algo subió el valor de respuesta
     * de /subir-imagen en vez de su `ruta`) ese Storage::url() la
     * duplica. Esta función normaliza cualquier valor que ya traiga el
     * prefijo del disco público, de forma idempotente.
     */
    public static function aRuta(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return $valor;
        }

        $prefijo = Storage::disk('public')->url('');

        return str_starts_with($valor, $prefijo) ? substr($valor, strlen($prefijo)) : $valor;
    }
}
