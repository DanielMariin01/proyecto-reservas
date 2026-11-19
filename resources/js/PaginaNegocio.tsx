import { useMemo, useState } from 'react';
import type { CSSProperties, ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { ShoppingCart } from 'lucide-react';
import { obtenerPagina } from '@/bloques/api/clientePagina';
import registroBloques from '@/bloques/registroBloques';
import type { OpcionesReserva } from '@/bloques/tipos';
import FlujoCompra from '@/flujo-compra/FlujoCompra';
import useCarrito from '@/flujo-compra/useCarrito';
import FlujoReserva from '@/flujo-reserva/FlujoReserva';

interface PropiedadesPaginaNegocio {
    slug: string;
}

export default function PaginaNegocio({ slug }: PropiedadesPaginaNegocio): ReactElement {
    const vistaPrevia = useMemo(() => new URLSearchParams(window.location.search).get('vista_previa') === '1', []);
    const [reserva, setReserva] = useState<OpcionesReserva | null>(null);
    const carrito = useCarrito();

    const paginaQuery = useQuery({
        queryKey: ['pagina', slug, vistaPrevia],
        queryFn: () => obtenerPagina(slug, vistaPrevia),
    });

    if (paginaQuery.isLoading) {
        return (
            <main className="min-h-screen bg-fondo-panel p-6">
                <div className="mx-auto max-w-5xl space-y-4">
                    <div className="h-64 animate-pulse rounded-tarjeta bg-gris-claro" />
                    <div className="h-32 animate-pulse rounded-tarjeta bg-gris-claro" />
                </div>
            </main>
        );
    }

    if (paginaQuery.isError || !paginaQuery.data) {
        return (
            <main className="flex min-h-screen items-center justify-center bg-fondo-panel p-6 text-center">
                <p className="text-sm text-texto-suave">Algo falló cargando esta página. Código REF-PAG1.</p>
            </main>
        );
    }

    const pagina = paginaQuery.data;

    /**
     * Skill diseno-reservas, sección 6: los colores de marca del negocio
     * sobrescriben --primario/--secundario solo dentro de su página
     * pública (incluido el flujo de reserva, que se renderiza dentro de
     * este mismo <main>). Si el negocio no tiene paleta, quedan los
     * tokens de sistema sin tocar.
     */
    const estiloMarca = {
        ...(pagina.colorPrimario ? { '--color-primario': pagina.colorPrimario } : {}),
        ...(pagina.colorSecundario ? { '--color-secundario': pagina.colorSecundario } : {}),
    } as CSSProperties;

    return (
        <main className="fuente-publica min-h-screen bg-blanco" style={estiloMarca}>
            {pagina.esVistaPrevia && (
                <div className="bg-acento p-2 text-center text-sm font-semibold text-blanco">
                    Vista previa — esto todavía no está publicado
                </div>
            )}

            {pagina.bloques.map((bloque, indice) => {
                const Bloque = registroBloques[bloque.tipo];

                if (!Bloque) {
                    return null;
                }

                return (
                    <Bloque
                        key={`${bloque.tipo}-${indice}`}
                        tipo={bloque.tipo}
                        datos={bloque.datos}
                        pagina={pagina}
                        slug={slug}
                        onReservar={setReserva}
                        onAgregarAlCarrito={carrito.agregar}
                    />
                );
            })}

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

            {reserva && (
                <FlujoReserva
                    slug={slug}
                    direccion={pagina.direccion}
                    servicioInicial={reserva.servicio ?? null}
                    profesionalInicial={reserva.profesionalId ?? null}
                    onCerrar={() => setReserva(null)}
                />
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
