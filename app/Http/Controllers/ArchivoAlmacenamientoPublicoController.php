<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Respaldo para servir storage/app/public cuando el servidor web no
 * resuelve el symlink public/storage como archivo estático (p. ej.
 * LiteSpeed en hosting compartido con "FollowSymLinks" restringido): la
 * petición cae al front controller de Laravel en vez de servirse
 * directo, así que la atendemos aquí con el mismo resultado.
 */
final class ArchivoAlmacenamientoPublicoController
{
    private const SEGUNDOS_DE_CACHE = 604_800;

    public function mostrar(string $ruta): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($ruta), 404);

        return Storage::disk('public')->response($ruta, headers: [
            'Cache-Control' => 'public, max-age='.self::SEGUNDOS_DE_CACHE,
        ]);
    }
}
