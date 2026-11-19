import { Search } from 'lucide-react';
import type { ReactElement } from 'react';

const COLOR_FONDO_CATEGORIAS_POR_DEFECTO = '#E1EEFF';
const COLOR_TEXTO_CATEGORIAS_POR_DEFECTO = '#673DE6';

export interface CategoriaFiltro {
    id: number;
    nombre: string;
}

interface Propiedades {
    busqueda?: string;
    onCambiarBusqueda?: (valor: string) => void;
    placeholderBusqueda?: string;
    categorias: CategoriaFiltro[];
    categoriaElegida: number | null;
    onElegirCategoria: (id: number | null) => void;
    colorFondoCategorias?: string;
    colorTextoCategorias?: string;
}

/**
 * Buscador + píldoras de categoría reutilizados entre Servicios,
 * Productos (bloque de portada, solo categorías) y la página de Tienda
 * (buscador + categorías) — misma lógica de filtro visual, un solo lugar.
 */
export default function BarraBusquedaYCategorias({
    busqueda,
    onCambiarBusqueda,
    placeholderBusqueda = 'Buscar...',
    categorias,
    categoriaElegida,
    onElegirCategoria,
    colorFondoCategorias = COLOR_FONDO_CATEGORIAS_POR_DEFECTO,
    colorTextoCategorias = COLOR_TEXTO_CATEGORIAS_POR_DEFECTO,
}: Propiedades): ReactElement {
    return (
        <div className="mb-6 space-y-3">
            {onCambiarBusqueda && (
                <div className="relative max-w-sm">
                    <Search size={18} strokeWidth={1.75} className="absolute left-3 top-1/2 -translate-y-1/2 text-texto-suave" />
                    <input
                        type="search"
                        value={busqueda ?? ''}
                        onChange={(evento) => onCambiarBusqueda(evento.target.value)}
                        placeholder={placeholderBusqueda}
                        aria-label={placeholderBusqueda}
                        className="h-11 w-full rounded-boton border border-gris-claro bg-blanco pl-10 pr-3 text-tinta focus:border-primario focus:outline-none"
                    />
                </div>
            )}

            {categorias.length > 1 && (
                <div className="flex flex-wrap gap-2">
                    <button
                        type="button"
                        onClick={() => onElegirCategoria(null)}
                        style={
                            categoriaElegida === null
                                ? { borderColor: colorTextoCategorias, backgroundColor: colorFondoCategorias, color: colorTextoCategorias }
                                : undefined
                        }
                        className={`inline-flex min-h-11 items-center rounded-full border px-4 text-base font-semibold ${
                            categoriaElegida === null ? '' : 'border-gris-claro text-texto-suave'
                        }`}
                    >
                        Todas
                    </button>
                    {categorias.map((categoria) => (
                        <button
                            key={categoria.id}
                            type="button"
                            onClick={() => onElegirCategoria(categoria.id)}
                            style={
                                categoriaElegida === categoria.id
                                    ? { borderColor: colorTextoCategorias, backgroundColor: colorFondoCategorias, color: colorTextoCategorias }
                                    : undefined
                            }
                            className={`inline-flex min-h-11 items-center rounded-full border px-4 text-base font-semibold ${
                                categoriaElegida === categoria.id ? '' : 'border-gris-claro text-texto-suave'
                            }`}
                        >
                            {categoria.nombre}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
