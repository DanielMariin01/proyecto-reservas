import { useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerPagina } from '@/bloques/api/clientePagina';
import registroBloques from '@/bloques/registroBloques';
import type { OpcionesReserva } from '@/bloques/tipos';
import FlujoReserva from '@/flujo-reserva/FlujoReserva';

interface PropiedadesPaginaNegocio {
    slug: string;
}

export default function PaginaNegocio({ slug }: PropiedadesPaginaNegocio): ReactElement {
    const vistaPrevia = useMemo(() => new URLSearchParams(window.location.search).get('vista_previa') === '1', []);
    const [reserva, setReserva] = useState<OpcionesReserva | null>(null);

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

    return (
        <main className="min-h-screen bg-blanco">
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
                    />
                );
            })}

            {reserva && (
                <FlujoReserva
                    slug={slug}
                    direccion={pagina.direccion}
                    servicioInicial={reserva.servicio ?? null}
                    profesionalInicial={reserva.profesionalId ?? null}
                    onCerrar={() => setReserva(null)}
                />
            )}
        </main>
    );
}
