import { Package } from 'lucide-react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerProductos } from '@/flujo-reserva/api/clientePublico';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueProductos({ datos, slug, onAgregarAlCarrito }: PropiedadesBloque): ReactElement {
    const productosQuery = useQuery({
        queryKey: ['productos', slug],
        queryFn: () => obtenerProductos(slug),
    });

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-[1200px] px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-3xl font-bold sm:text-4xl">Productos</h2>

                {productosQuery.isLoading && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Cargando productos">
                        {[1, 2, 3].map((n) => (
                            <div key={n} className="h-64 animate-pulse rounded-tarjeta bg-gris-claro" />
                        ))}
                    </div>
                )}

                {productosQuery.isError && <p className="text-sm text-red-600">Algo falló cargando los productos. Código REF-PRO1.</p>}

                {productosQuery.data && productosQuery.data.length === 0 && (
                    <p className="text-sm opacity-80">Este negocio aún no tiene productos disponibles.</p>
                )}

                {productosQuery.data && productosQuery.data.length > 0 && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {productosQuery.data.map((producto) => (
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
            </div>
        </section>
    );
}
