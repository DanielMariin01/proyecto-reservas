<?php

declare(strict_types=1);

namespace App\Http\Resources\Cliente;

use App\Models\CuentaCliente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CuentaCliente
 */
final class RecursoCuentaCliente extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nombre' => $this->nombre,
            'correo' => $this->correo,
        ];
    }
}
