<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Pagina;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pagina
 */
final class RecursoPaginaWeb extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'bloques' => $this->bloques,
            'estaPublicada' => $this->estaPublicada(),
            'publicadaEn' => $this->publicada_en?->format('d/m/Y H:i'),
            'hayCambiosSinPublicar' => $this->bloques !== $this->bloques_publicados,
        ];
    }
}
