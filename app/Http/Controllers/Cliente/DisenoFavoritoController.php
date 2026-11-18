<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Actions\AlternarDisenoFavorito;
use App\Http\Controllers\Controller;
use App\Models\CuentaCliente;
use App\Models\DisenoFavorito;
use App\Models\Negocio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class DisenoFavoritoController extends Controller
{
    public function __construct(
        private readonly AlternarDisenoFavorito $alternarDisenoFavorito,
    ) {}

    /**
     * La galería de "Diseños favoritos" en /mi-cuenta (RF-HCL-08-b): un
     * favorito se omite en silencio si el ítem ya no existe en la página
     * publicada del negocio (el super admin lo borró o cambió de
     * disposición después de que el cliente lo guardó).
     */
    public function index(): JsonResponse
    {
        $favoritos = DisenoFavorito::query()
            ->where('cuenta_cliente_id', $this->cuentaActual()->id)
            ->with('negocio.pagina')
            ->orderByDesc('created_at')
            ->get();

        $datos = $favoritos
            ->map(fn (DisenoFavorito $favorito): ?array => $this->datosDelFavorito($favorito))
            ->filter()
            ->values();

        return response()->json(['data' => $datos]);
    }

    /**
     * Los ids ya favoritos de un negocio puntual, para que el bloque
     * público `mis_disenos` sepa qué corazón mostrar lleno al cargar.
     */
    public function idsDelNegocio(Negocio $negocio): JsonResponse
    {
        $ids = DisenoFavorito::query()
            ->where('cuenta_cliente_id', $this->cuentaActual()->id)
            ->where('negocio_id', $negocio->id)
            ->pluck('diseno_id');

        return response()->json(['data' => $ids]);
    }

    public function alternar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'negocio_slug' => ['required', 'string'],
            'diseno_id' => ['required', 'string'],
        ]);

        $negocio = Negocio::query()->where('slug', $datos['negocio_slug'])->firstOrFail();

        $quedoFavorito = $this->alternarDisenoFavorito->ejecutar($this->cuentaActual(), $negocio, $datos['diseno_id']);

        return response()->json(['favorito' => $quedoFavorito]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function datosDelFavorito(DisenoFavorito $favorito): ?array
    {
        $negocio = $favorito->negocio;

        if ($negocio === null) {
            return null;
        }

        $pagina = $negocio->pagina;
        $bloques = $pagina === null ? [] : ($pagina->bloques_publicados ?? []);

        foreach ($bloques as $bloque) {
            if (($bloque['type'] ?? null) !== 'mis_disenos') {
                continue;
            }

            foreach ($bloque['data']['items'] ?? [] as $item) {
                if (($item['id'] ?? null) === $favorito->diseno_id) {
                    return [
                        'negocioSlug' => $negocio->slug,
                        'negocioNombre' => $negocio->nombre,
                        'disenoId' => $favorito->diseno_id,
                        'imagen' => is_string($item['imagen'] ?? null) ? Storage::disk('public')->url($item['imagen']) : null,
                        'titulo' => $item['titulo'] ?? null,
                        'descripcion' => $item['descripcion'] ?? null,
                    ];
                }
            }
        }

        return null;
    }

    private function cuentaActual(): CuentaCliente
    {
        /** @var CuentaCliente $cuenta */
        $cuenta = Auth::guard('cliente')->user();

        return $cuenta;
    }
}
