import { Scissors } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerServicios } from '@/flujo-reserva/api/clientePublico';
import type { Servicio } from '@/tipos';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import BarraBusquedaYCategorias, { type CategoriaFiltro } from './BarraBusquedaYCategorias';
import { obtenerColoresLibres, obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

const CLASES_FOTO_SERVICIO = 'h-24 w-24 shrink-0 rounded-boton object-cover sm:h-32 sm:w-32';
const COLOR_FONDO_CATEGORIAS_POR_DEFECTO = '#E1EEFF';
const COLOR_TEXTO_CATEGORIAS_POR_DEFECTO = '#673DE6';

function leerColorTexto(datos: Record<string, unknown>, campo: string, porDefecto: string): string {
    return typeof datos[campo] === 'string' && datos[campo] !== '' ? (datos[campo] as string) : porDefecto;
}

/**
 * Categorías distintas presentes en los servicios ya cargados, en el orden
 * en que aparecen — no hay un endpoint propio de categorías todavía, así
 * que la barra de filtro se construye con los mismos datos del bloque.
 */
function categoriasDeServicios(servicios: Servicio[]): CategoriaFiltro[] {
    const categorias = new Map<number, string>();

    for (const servicio of servicios) {
        if (servicio.categoriaServicioId !== null && servicio.categoria !== null && !categorias.has(servicio.categoriaServicioId)) {
            categorias.set(servicio.categoriaServicioId, servicio.categoria);
        }
    }

    return Array.from(categorias, ([id, nombre]) => ({ id, nombre }));
}

function coincideConBusqueda(servicio: Servicio, busqueda: string): boolean {
    const texto = busqueda.trim().toLowerCase();

    if (texto === '') {
        return true;
    }

    return servicio.nombre.toLowerCase().includes(texto) || (servicio.descripcion?.toLowerCase().includes(texto) ?? false);
}

/**
 * `servicio.foto` puede apuntar a un archivo que ya no existe en disco
 * (borrado aparte, symlink de storage distinto en otro entorno, etc.) —
 * en vez de confiar en el string y mostrar el ícono roto del navegador,
 * se cae al mismo respaldo que cuando no hay foto.
 */
function FotoServicio({ foto }: { foto: string | null }): ReactElement {
    const [fallo, setFallo] = useState(false);

    if (!foto || fallo) {
        return (
            <span className={`flex items-center justify-center bg-fondo-panel text-primario ${CLASES_FOTO_SERVICIO}`}>
                <Scissors size={28} strokeWidth={1.75} />
            </span>
        );
    }

    return <img src={foto} alt="" onError={() => setFallo(true)} className={CLASES_FOTO_SERVICIO} />;
}

export default function BloqueServicios({ datos, slug, onReservar }: PropiedadesBloque): ReactElement {
    const [busqueda, setBusqueda] = useState('');
    const [categoriaElegida, setCategoriaElegida] = useState<number | null>(null);

    const serviciosQuery = useQuery({
        queryKey: ['servicios', slug],
        queryFn: () => obtenerServicios(slug),
    });

    const categorias = useMemo(() => categoriasDeServicios(serviciosQuery.data ?? []), [serviciosQuery.data]);

    const { colorTexto } = obtenerColoresLibres(datos);
    const colorTitulo = leerColorTexto(datos, 'colorTitulo', colorTexto);
    const colorFondoCategorias = leerColorTexto(datos, 'colorFondoCategorias', COLOR_FONDO_CATEGORIAS_POR_DEFECTO);
    const colorTextoCategorias = leerColorTexto(datos, 'colorTextoCategorias', COLOR_TEXTO_CATEGORIAS_POR_DEFECTO);

    const serviciosFiltrados = useMemo(() => {
        return (serviciosQuery.data ?? []).filter(
            (servicio) =>
                (categoriaElegida === null || servicio.categoriaServicioId === categoriaElegida) && coincideConBusqueda(servicio, busqueda),
        );
    }, [serviciosQuery.data, categoriaElegida, busqueda]);

    function limpiarFiltros(): void {
        setBusqueda('');
        setCategoriaElegida(null);
    }

    return (
        <section id="servicios" style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-[1200px] px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-3xl font-bold sm:text-4xl" style={{ color: colorTitulo }}>
                    Servicios
                </h2>

                {serviciosQuery.data && serviciosQuery.data.length > 0 && (
                    <BarraBusquedaYCategorias
                        busqueda={busqueda}
                        onCambiarBusqueda={setBusqueda}
                        placeholderBusqueda="Buscar servicio..."
                        categorias={categorias}
                        categoriaElegida={categoriaElegida}
                        onElegirCategoria={setCategoriaElegida}
                        colorFondoCategorias={colorFondoCategorias}
                        colorTextoCategorias={colorTextoCategorias}
                    />
                )}

                {serviciosQuery.isLoading && (
                    <div className="grid gap-4 md:grid-cols-3" aria-label="Cargando servicios">
                        {[1, 2, 3].map((n) => (
                            <div key={n} className="h-32 animate-pulse rounded-tarjeta bg-gris-claro" />
                        ))}
                    </div>
                )}

                {serviciosQuery.isError && <p className="text-sm text-red-600">Algo falló cargando los servicios. Código REF-SRV1.</p>}

                {serviciosQuery.data && serviciosQuery.data.length === 0 && (
                    <p className="text-sm opacity-80">Este negocio aún no tiene servicios disponibles.</p>
                )}

                {serviciosQuery.data && serviciosQuery.data.length > 0 && serviciosFiltrados.length === 0 && (
                    <div className="flex flex-col items-start gap-3 py-4">
                        <p className="text-base">No encontramos servicios con esos filtros.</p>
                        <Boton variante="contorno" onClick={limpiarFiltros}>
                            Limpiar filtros
                        </Boton>
                    </div>
                )}

                {serviciosFiltrados.length > 0 && (
                    <div className="grid gap-4 md:grid-cols-3">
                        {serviciosFiltrados.map((servicio) => (
                            <Tarjeta key={servicio.id} className="flex flex-row gap-4">
                                <FotoServicio foto={servicio.foto} />

                                <div className="flex min-w-0 flex-1 flex-col">
                                    {servicio.categoria && (
                                        <span className="mb-1 w-fit rounded-full bg-celeste px-2 py-0.5 text-xs font-semibold text-primario">
                                            {servicio.categoria}
                                        </span>
                                    )}
                                    <p className="font-semibold text-tinta">{servicio.nombre}</p>
                                    {servicio.descripcion && (
                                        <p className="mt-1 line-clamp-2 text-sm text-texto-suave">{servicio.descripcion}</p>
                                    )}
                                    <p className="mt-2 text-sm text-texto-suave">
                                        ${servicio.precio.toLocaleString('es-CO')} · {servicio.duracionMinutos} min
                                    </p>
                                    <Boton className="mt-auto w-full sm:w-fit" onClick={() => onReservar({ servicio })}>
                                        Agendar
                                    </Boton>
                                </div>
                            </Tarjeta>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
