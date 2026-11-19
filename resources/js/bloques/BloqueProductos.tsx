import { Package } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerProductos } from '@/flujo-reserva/api/clientePublico';
import type { Producto } from '@/tipos';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import BarraBusquedaYCategorias, { type CategoriaFiltro } from './BarraBusquedaYCategorias';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

const MAXIMO_PRODUCTOS_EN_PORTADA = 8;

/**
 * Igual que categoriasDeServicios (BloqueServicios): categorías distintas
 * presentes en los productos ya cargados, sin endpoint propio.
 */
function categoriasDeProductos(productos: Producto[]): CategoriaFiltro[] {
    const categorias = new Map<number, string>();

    for (const producto of productos) {
        if (producto.categoriaProductoId !== null && producto.categoria !== null && !categorias.has(producto.categoriaProductoId)) {
            categorias.set(producto.categoriaProductoId, producto.categoria);
        }
    }

    return Array.from(categorias, ([id, nombre]) => ({ id, nombre }));
}

export default function BloqueProductos({ datos, slug, onAgregarAlCarrito }: PropiedadesBloque): ReactElement {
    const [categoriaElegida, setCategoriaElegida] = useState<number | null>(null);

    const productosQuery = useQuery({
        queryKey: ['productos', slug],
        queryFn: () => obtenerProductos(slug),
    });

    const productosVisibles = useMemo(() => productosQuery.data ?? [], [productosQuery.data]);
    const categorias = useMemo(() => categoriasDeProductos(productosVisibles), [productosVisibles]);

    const productosFiltrados = useMemo(
        () => productosVisibles.filter((producto) => categoriaElegida === null || producto.categoriaProductoId === categoriaElegida),
        [productosVisibles, categoriaElegida],
    );

    const productosAMostrar = productosFiltrados.slice(0, MAXIMO_PRODUCTOS_EN_PORTADA);
    const mostrarBotonVerTodos = productosVisibles.length > MAXIMO_PRODUCTOS_EN_PORTADA;

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-[1200px] px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-3xl font-bold sm:text-4xl">Productos</h2>

                {productosVisibles.length > 0 && (
                    <BarraBusquedaYCategorias
                        categorias={categorias}
                        categoriaElegida={categoriaElegida}
                        onElegirCategoria={setCategoriaElegida}
                    />
                )}

                {productosQuery.isLoading && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Cargando productos">
                        {[1, 2, 3, 4].map((n) => (
                            <div key={n} className="h-64 animate-pulse rounded-tarjeta bg-gris-claro" />
                        ))}
                    </div>
                )}

                {productosQuery.isError && <p className="text-sm text-red-600">Algo falló cargando los productos. Código REF-PRO1.</p>}

                {productosQuery.data && productosQuery.data.length === 0 && (
                    <p className="text-sm opacity-80">Este negocio aún no tiene productos disponibles.</p>
                )}

                {productosAMostrar.length > 0 && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {productosAMostrar.map((producto) => (
                            <Tarjeta key={producto.id}>
                                {producto.foto ? (
                                    <img src={producto.foto} alt="" className="mb-3 aspect-square w-full rounded-boton object-cover" />
                                ) : (
                                    <div className="mb-3 flex aspect-square w-full items-center justify-center rounded-boton bg-fondo-panel text-primario">
                                        <Package size={32} strokeWidth={1.75} />
                                    </div>
                                )}
                                <p className="font-semibold text-tinta">{producto.nombre}</p>
                                {producto.descripcion && <p className="mt-1 text-sm text-texto-suave">{producto.descripcion}</p>}
                                <p className="mt-2 text-sm text-texto-suave">${producto.precio.toLocaleString('es-CO')}</p>
                                <Boton className="mt-3 w-full" onClick={() => onAgregarAlCarrito(producto)}>
                                    Agregar al carrito
                                </Boton>
                            </Tarjeta>
                        ))}
                    </div>
                )}

                {mostrarBotonVerTodos && (
                    <div className="mt-8 flex justify-center">
                        <Boton variante="contorno" onClick={() => (window.location.href = `/${slug}/tienda`)}>
                            Ver todos
                        </Boton>
                    </div>
                )}
            </div>
        </section>
    );
}
