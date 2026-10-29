<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\Models\Negocio;
use App\Models\Pagina;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class RecursoPaginaPublica extends JsonResource
{
    public function __construct(
        private readonly Negocio $negocio,
        private readonly ?Pagina $pagina,
        private readonly bool $mostrarBorrador,
    ) {
        parent::__construct($pagina);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bloquesCrudos = $this->pagina === null
            ? []
            : ($this->mostrarBorrador ? $this->pagina->bloques : ($this->pagina->bloques_publicados ?? []));

        return [
            'nombreNegocio' => $this->negocio->nombre,
            'tipo' => $this->negocio->tipo->value,
            'tipoEtiqueta' => $this->negocio->tipo->etiqueta(),
            'logo' => $this->negocio->logo !== null ? Storage::disk('public')->url($this->negocio->logo) : null,
            'colorPrimario' => $this->negocio->paletaMarca?->color_primario,
            'colorSecundario' => $this->negocio->paletaMarca?->color_secundario,
            'direccion' => $this->negocio->direccion,
            'whatsapp' => $this->negocio->whatsapp,
            'redesSociales' => $this->negocio->redes_sociales ?? [],
            'bloques' => $this->traducirBloques($bloquesCrudos),
            'esVistaPrevia' => $this->mostrarBorrador,
        ];
    }

    /**
     * El componente Builder de Filament guarda cada bloque como
     * {type, data} (claves fijas del paquete). La API pública y React
     * usan {tipo, datos}, como el resto del proyecto.
     *
     * @param  array<int, array{type?: string, data?: array<string, mixed>}>  $bloquesCrudos
     * @return list<array{tipo: string, datos: array<string, mixed>}>
     */
    private function traducirBloques(array $bloquesCrudos): array
    {
        return array_values(array_map(
            fn (array $bloque): array => [
                'tipo' => $bloque['type'] ?? '',
                'datos' => $this->traducirRutasDeArchivo($bloque['data'] ?? []),
            ],
            $bloquesCrudos,
        ));
    }

    /**
     * `imagen` y `fotos` guardan la ruta relativa del disco `public`
     * (igual que Servicio::foto en RecursoServicioPublico), no la URL.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function traducirRutasDeArchivo(array $datos): array
    {
        if (isset($datos['imagen']) && is_string($datos['imagen'])) {
            $datos['imagen'] = Storage::disk('public')->url($datos['imagen']);
        }

        if (isset($datos['fotos']) && is_array($datos['fotos'])) {
            $datos['fotos'] = array_map(
                fn (mixed $foto): mixed => is_string($foto) ? Storage::disk('public')->url($foto) : $foto,
                $datos['fotos'],
            );
        }

        // `testimonios` y `tarjetas` guardan la foto de cada item en una
        // ruta anidada (datos.testimonios[].foto, datos.tarjetas[].imagen),
        // no en las claves de nivel superior que cubre el resto de este
        // método.
        $datos = $this->traducirRutaAnidada($datos, 'testimonios', 'foto');
        $datos = $this->traducirRutaAnidada($datos, 'tarjetas', 'imagen');

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function traducirRutaAnidada(array $datos, string $campoLista, string $campoRuta): array
    {
        if (! isset($datos[$campoLista]) || ! is_array($datos[$campoLista])) {
            return $datos;
        }

        $datos[$campoLista] = array_map(
            function (mixed $item) use ($campoRuta): mixed {
                if (! is_array($item) || ! isset($item[$campoRuta]) || ! is_string($item[$campoRuta])) {
                    return $item;
                }

                $item[$campoRuta] = Storage::disk('public')->url($item[$campoRuta]);

                return $item;
            },
            $datos[$campoLista],
        );

        return $datos;
    }
}
