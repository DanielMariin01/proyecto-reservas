<?php

declare(strict_types=1);

namespace App\Observabilidad;

use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * CLAUDE.md, sección Observabilidad: cada error y log debe incluir
 * negocio_id, usuario_id e id_peticion. Este servicio centraliza cómo se
 * obtienen esos tres datos para no tener que inyectarlos a mano en cada
 * sitio que loguea algo (ver App\Logging\AgregarContextoObservabilidad).
 *
 * negocio_id se resuelve de forma perezosa, no al construir el objeto:
 * según la ruta, el negocio se conoce por ContextoNegocioActual (panel
 * `app`) o por el parámetro `{negocio:slug}` de la API pública, y
 * ninguno de los dos está resuelto todavía cuando arranca el middleware.
 *
 * idPeticion() también es perezoso: AsignarIdPeticion lo fija al inicio
 * de cada petición web, pero fuera del pipeline HTTP (un comando de
 * consola, un trabajo en cola) nada lo fija nunca, y sin esto
 * GenerarCodigoDeReferencia recibía una cadena vacía y producía el
 * código inválido "REF-". Generarlo aquí mismo si nadie lo fijó antes
 * garantiza que todo log o error tenga siempre uno, sin importar el
 * contexto. Para colas, QueueServiceProvider::boot() (AppServiceProvider)
 * llama a definirIdPeticion() de nuevo en cada trabajo — este servicio es
 * singleton y un worker de cola vive mucho más que un solo trabajo, así
 * que sin ese reinicio todos los trabajos de un mismo proceso de worker
 * compartirían el id generado por el primero.
 */
final class ContextoObservabilidad
{
    private ?string $idPeticion = null;

    public function __construct(private readonly Request $peticion) {}

    public function definirIdPeticion(string $idPeticion): void
    {
        $this->idPeticion = $idPeticion;
    }

    public function idPeticion(): string
    {
        return $this->idPeticion ??= (string) Str::uuid();
    }

    public function negocioId(): ?int
    {
        try {
            return Negocio::actual()->id;
        } catch (Throwable) {
            // No hay negocio activo en el contexto (API pública u otra ruta).
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
     * @return array{id_peticion: string, negocio_id: ?int, usuario_id: ?int}
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
