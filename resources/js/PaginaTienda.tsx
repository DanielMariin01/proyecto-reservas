import { Package, ShoppingCart } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { CSSProperties, ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerPagina } from '@/bloques/api/clientePagina';
import BarraBusquedaYCategorias, { type CategoriaFiltro } from '@/bloques/BarraBusquedaYCategorias';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import FlujoCompra from '@/flujo-compra/FlujoCompra';
import useCarrito from '@/flujo-compra/useCarrito';
import { obtenerProductos } from '@/flujo-reserva/api/clientePublico';
import type { Producto } from '@/tipos';

interface PropiedadesPaginaTienda {
    slug: string;
}

function categoriasDeProductos(productos: Producto[]): CategoriaFiltro[] {
    const categorias = new Map<number, string>();

    for (const producto of productos) {
        if (producto.categoriaProductoId !== null && producto.categoria !== null && !categorias.has(producto.categoriaProductoId)) {
            categorias.set(producto.categoriaProductoId, producto.categoria);
        }
    }

    return Array.from(categorias, ([id, nombre]) => ({ id, nombre }));
}

function coincideConBusqueda(producto: Producto, busqueda: string): boolean {
    const texto = busqueda.trim().toLowerCase();

    if (texto === '') {
        return true;
    }

    return producto.nombre.toLowerCase().includes(texto) || (producto.descripcion?.toLowerCase().includes(texto) ?? false);
}

export default function PaginaTienda({ slug }: PropiedadesPaginaTienda): ReactElement {
    const [busqueda, setBusqueda] = useState('');
    const [categoriaElegida, setCategoriaElegida] = useState<number | null>(null);
    const carrito = useCarrito();

    const paginaQuery = useQuery({
        queryKey: ['pagina', slug, false],
        queryFn: () => obtenerPagina(slug, false),
    });

    const productosQuery = useQuery({
        queryKey: ['productos', slug],
        queryFn: () => obtenerProductos(slug),
    });

    const productos = useMemo(() => productosQuery.data ?? [], [productosQuery.data]);
    const categorias = useMemo(() => categoriasDeProductos(productos), [productos]);

    const productosFiltrados = useMemo(
        () =>
            productos.filter(
                (producto) => (categoriaElegida === null || producto.categoriaProductoId === categoriaElegida) && coincideConBusqueda(producto, busqueda),
            ),
        [productos, categoriaElegida, busqueda],
    );

    function limpiarFiltros(): void {
        setBusqueda('');
        setCategoriaElegida(null);
    }

    if (paginaQuery.isLoading) {
        return (
            <main className="min-h-screen bg-fondo-panel p-6">
                <div className="mx-auto max-w-5xl space-y-4">
                    <div className="h-16 animate-pulse rounded-tarjeta bg-gris-claro" />
                    <div className="h-64 animate-pulse rounded-tarjeta bg-gris-claro" />
                </div>
            </main>
        );
    }

    if (paginaQuery.isError || !paginaQuery.data) {
        return (
            <main className="flex min-h-screen items-center justify-center bg-fondo-panel p-6 text-center">
                <p className="text-sm text-texto-suave">Algo falló cargando esta página. Código REF-TDA1.</p>
            </main>
        );
    }

    const pagina = paginaQuery.data;

    const estiloMarca = {
        ...(pagina.colorPrimario ? { '--color-primario': pagina.colorPrimario } : {}),
        ...(pagina.colorSecundario ? { '--color-secundario': pagina.colorSecundario } : {}),
    } as CSSProperties;

    return (
        <main className="fuente-publica min-h-screen bg-blanco" style={estiloMarca}>
            <header className="border-b border-gris-claro">
                <div className="mx-auto flex max-w-[1200px] items-center justify-between gap-4 px-4 py-4">
                    {pagina.logo ? (
                        <img src={pagina.logo} alt={pagina.nombreNegocio} className="h-10 w-auto object-contain" />
                    ) : (
                        <span className="font-bold text-tinta">{pagina.nombreNegocio}</span>
                    )}
                    <a href={`/${slug}`} className="text-sm text-primario hover:underline">
                        ← Volver a la página
                    </a>
                </div>
            </header>

            <div className="mx-auto max-w-[1200px] px-4 py-10 sm:py-16">
                <h1 className="mb-6 text-3xl font-bold text-tinta sm:text-4xl">Tienda</h1>

                {productos.length > 0 && (
                    <BarraBusquedaYCategorias
                        busqueda={busqueda}
                        onCambiarBusqueda={setBusqueda}
                        placeholderBusqueda="Buscar producto..."
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

                {productosQuery.isError && <p className="text-sm text-red-600">Algo falló cargando los productos. Código REF-TDA2.</p>}

                {productosQuery.data && productosQuery.data.length === 0 && (
                    <p className="text-sm text-texto-suave">Este negocio aún no tiene productos disponibles.</p>
                )}

                {productos.length > 0 && productosFiltrados.length === 0 && (
                    <div className="flex flex-col items-start gap-3 py-4">
                        <p className="text-base text-tinta">No encontramos productos con esos filtros.</p>
                        <Boton variante="contorno" onClick={limpiarFiltros}>
                            Limpiar filtros
                        </Boton>
                    </div>
                )}

                {productosFiltrados.length > 0 && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {productosFiltrados.map((producto) => (
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
                                <Boton className="mt-3 w-full" onClick={() => carrito.agregar(producto)}>
                                    Agregar al carrito
                                </Boton>
                            </Tarjeta>
                        ))}
                    </div>
                )}
            </div>

            {!carrito.mostrar && carrito.items.length > 0 && (
                <button
                    type="button"
                    onClick={carrito.abrir}
                    aria-label="Ver carrito"
                    className="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-primario px-5 py-3 text-blanco shadow-tarjeta"
                >
                    <ShoppingCart size={20} strokeWidth={1.75} />
                    <span className="font-semibold">{carrito.totalDeItems}</span>
                </button>
            )}

            {carrito.mostrar && (
                <FlujoCompra
                    slug={slug}
                    direccion={pagina.direccion}
                    carrito={carrito.items}
                    onCambiarCantidad={carrito.cambiarCantidad}
                    onQuitarItem={carrito.quitar}
                    onCerrar={carrito.cerrar}
                    onPedidoCreado={carrito.vaciar}
                />
            )}
        </main>
    );
}
