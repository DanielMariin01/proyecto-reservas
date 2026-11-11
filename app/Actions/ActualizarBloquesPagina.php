<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Negocio;
use App\Models\Pagina;

final class ActualizarBloquesPagina
{
    /**
     * @param  list<array{type: string, data: array<string, mixed>}>  $bloques
     */
    public function ejecutar(Negocio $negocio, array $bloques): Pagina
    {
        return Pagina::query()->updateOrCreate(
            ['negocio_id' => $negocio->id],
            ['bloques' => $bloques],
        );
    }
}
