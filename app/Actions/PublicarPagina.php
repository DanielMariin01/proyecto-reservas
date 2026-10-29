<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\PaginaPublicada;
use App\Models\Pagina;
use App\Models\Usuario;
use Carbon\CarbonImmutable;

/**
 * RF-PAG-06: vista previa antes de publicar. Esta acción es el "publicar":
 * copia el borrador que se estuvo editando a lo que la API pública sirve.
 */
final class PublicarPagina
{
    public function ejecutar(Pagina $pagina, ?Usuario $publicadaPor = null): Pagina
    {
        $pagina->update([
            'bloques_publicados' => $pagina->bloques,
            'publicada_en' => CarbonImmutable::now(),
        ]);

        PaginaPublicada::dispatch($pagina, $publicadaPor);

        return $pagina;
    }
}
