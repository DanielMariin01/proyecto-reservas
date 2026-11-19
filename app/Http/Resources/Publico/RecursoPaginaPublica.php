<?php

declare(strict_types=1);

namespace App\Http\Resources\Publico;

use App\DatosTransferencia\DatosPopularidadServicio;
use App\Enums\EstadoResenaValor;
use App\Enums\FuenteTestimonios;
use App\Enums\TipoBloque;
use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\Pagina;
use App\Models\Resena;
use App\Models\Servicio;
use App\Services\ServicioPopularidadServicios;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class RecursoPaginaPublica extends JsonResource
{
    private const CANTIDAD_SERVICIOS_DESTACADOS_POR_DEFECTO = 4;

    private const MINIMO_SERVICIOS_DESTACADOS = 2;

    private const MAXIMO_SERVICIOS_DESTACADOS = 8;

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
            function (array $bloque): array {
                $tipo = $bloque['type'] ?? '';
                $datos = $this->traducirRutasDeArchivo($bloque['data'] ?? []);

                if ($tipo === TipoBloque::Testimonios->value && ($datos['fuente'] ?? null) === FuenteTestimonios::Sistema->value) {
                    $datos['testimonios'] = $this->testimoniosDelSistema();
                    $datos['resumen'] = $this->resumenResenas();
                }

                if ($tipo === TipoBloque::ServiciosDestacados->value) {
                    $datos['servicios'] = $this->serviciosDestacados($datos);
                }

                return ['tipo' => $tipo, 'datos' => $datos];
            },
            $bloquesCrudos,
        ));
    }

    /**
     * Resuelve el bloque `testimonios` con fuente 'sistema' (App\Enums\
     * FuenteTestimonios) a partir de las reseñas reales y publicadas del
     * negocio: misma forma que un testimonio manual (cita, nombre, foto,
     * estrellas), así React no distingue el origen.
     *
     * @return list<array{cita: string, nombre: string, foto: null, estrellas: int}>
     */
    private function testimoniosDelSistema(): array
    {
        $resenas = Resena::query()
            ->where('negocio_id', $this->negocio->id)
            ->where('estado', EstadoResenaValor::Publicada)
            ->orderByDesc('calificacion')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return array_values($resenas->map(fn (Resena $resena): array => [
            'cita' => $resena->comentario,
            'nombre' => Cliente::findOrFail($resena->cliente_id)->nombre,
            'foto' => null,
            'estrellas' => $resena->calificacion,
        ])->all());
    }

    /**
     * `promedio_calificacion`/`total_resenas` ya se mantienen cacheados en
     * `Negocio` por `ServicioResenas::recalcularPromedios` (patrón
     * Observador, ver `ActualizarPromedioDeCalificaciones`) — aquí solo se
     * exponen, sin agregación nueva.
     *
     * @return array{promedio: float|null, total: int}
     */
    private function resumenResenas(): array
    {
        return [
            'promedio' => $this->negocio->promedio_calificacion,
            'total' => $this->negocio->total_resenas,
        ];
    }

    /**
     * Resuelve el bloque `servicios_destacados`: toma el ranking real de
     * `ServicioPopularidadServicios` y lo combina con los datos del
     * servicio (igual forma que `RecursoServicioPublico`, para que React
     * reutilice el mismo tipo `Servicio`), descartando cualquier servicio
     * que ya no esté visible aunque siga apareciendo en citas pasadas.
     *
     * @param  array<string, mixed>  $datos
     * @return array<int, array<string, mixed>>
     */
    private function serviciosDestacados(array $datos): array
    {
        $cantidad = is_int($datos['cantidad'] ?? null)
            ? max(self::MINIMO_SERVICIOS_DESTACADOS, min(self::MAXIMO_SERVICIOS_DESTACADOS, $datos['cantidad']))
            : self::CANTIDAD_SERVICIOS_DESTACADOS_POR_DEFECTO;

        $ranking = app(ServicioPopularidadServicios::class)->topServicios($this->negocio, $cantidad);

        $servicios = Servicio::query()
            ->where('negocio_id', $this->negocio->id)
            ->where('visible', true)
            ->whereIn('id', $ranking->pluck('servicioId'))
            ->with('categoriaServicio')
            ->get()
            ->keyBy('id');

        return $ranking
            ->filter(fn (DatosPopularidadServicio $fila): bool => $servicios->has($fila->servicioId))
            ->map(fn (DatosPopularidadServicio $fila): array => [
                ...RecursoServicioPublico::make($servicios->get($fila->servicioId))->resolve(),
                'puesto' => $fila->puesto,
                'totalReservas' => $fila->totalReservas,
                'clientesDistintos' => $fila->clientesDistintos,
            ])
            ->values()
            ->all();
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

        // `testimonios`, `tarjetas` y `mis_disenos` guardan la foto de cada
        // item en una ruta anidada (datos.testimonios[].foto,
        // datos.tarjetas[].imagen, datos.items[].imagen), no en las claves
        // de nivel superior que cubre el resto de este método.
        $datos = $this->traducirRutaAnidada($datos, 'testimonios', 'foto');
        $datos = $this->traducirRutaAnidada($datos, 'tarjetas', 'imagen');
        $datos = $this->traducirRutaAnidada($datos, 'items', 'imagen');

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
