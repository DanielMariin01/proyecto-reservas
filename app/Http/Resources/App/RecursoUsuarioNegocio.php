<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

/**
 * @mixin Usuario
 */
final class RecursoUsuarioNegocio extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Role|null $rol */
        $rol = $this->roles->first();

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'rol' => $rol?->name,
        ];
    }
}
