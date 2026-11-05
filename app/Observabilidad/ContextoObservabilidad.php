<?php

declare(strict_types=1);

namespace App\Observabilidad;

use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * CLAUDE.md, sección Observabilidad: cada error y log debe incluir
 * negocio_id, usuario_id e id_peticion. Este servicio centraliza cómo se
 * obtienen esos tres datos para no tener que inyectarlos a mano en cada
 * sitio que loguea algo (ver App\Logging\AgregarContextoObservabilidad).
 *
 * negocio_id se resuelve de forma perezosa, no al construir el objeto:
 * según la ruta, el negocio se conoce por el tenant de Filament (panel
 * `app`) o por el parámetro `{negocio:slug}` de la API pública, y
 * ninguno de los dos está resuelto todavía cuando arranca el middleware.
 */
final class ContextoObservabilidad
{
    private ?string $idPeticion = null;

    public function __construct(private readonly Request $peticion) {}

    public function definirIdPeticion(string $idPeticion): void
    {
        $this->idPeticion = $idPeticion;
    }

    public function idPeticion(): ?string
    {
        return $this->idPeticion;
    }

    public function negocioId(): ?int
    {
        try {
            return Negocio::actual()->id;
        } catch (Throwable) {
            // No hay tenant de Filament activo (API pública u otra ruta).
        }

        $negocio = $this->peticion->route('negocio');

        return $negocio instanceof Negocio ? $negocio->id : null;
    }

    public function usuarioId(): ?int
    {
        $id = Auth::id();

        return is_int($id) ? $id : null;
    }

    /**
     * @return array{id_peticion: ?string, negocio_id: ?int, usuario_id: ?int}
     */
    public function datos(): array
    {
        return [
            'id_peticion' => $this->idPeticion(),
            'negocio_id' => $this->negocioId(),
            'usuario_id' => $this->usuarioId(),
        ];
    }
}
