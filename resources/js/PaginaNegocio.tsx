import { useState } from 'react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import FlujoReserva from '@/flujo-reserva/FlujoReserva';
import { obtenerServicios } from '@/flujo-reserva/api/clientePublico';
import type { Servicio } from '@/tipos';

interface PropiedadesPaginaNegocio {
    slug: string;
    nombreNegocio: string;
    direccion: string | null;
}

/**
 * Página mínima para T6: la página armable por bloques (portada, equipo,
 * galería...) es T8. Aquí solo se demuestra el flujo completo de reserva
 * de principio a fin, consumiendo la API pública.
 */
export default function PaginaNegocio({ slug, nombreNegocio, direccion }: PropiedadesPaginaNegocio): ReactElement {
    const [servicioElegido, setServicioElegido] = useState<Servicio | null>(null);

    const serviciosQuery = useQuery({
        queryKey: ['servicios', slug],
        queryFn: () => obtenerServicios(slug),
    });

    return (
        <main className="min-h-screen bg-fondo-panel">
            <header className="border-b border-gris-claro bg-blanco p-6">
                <h1 className="text-2xl font-bold text-tinta">{nombreNegocio}</h1>
                {direccion && <p className="text-sm text-texto-suave">{direccion}</p>}
            </header>

            <section className="mx-auto max-w-5xl p-6">
                <h2 className="mb-4 text-xl font-semibold text-tinta">Servicios</h2>

                {serviciosQuery.isLoading && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Cargando servicios">
                        {[1, 2, 3].map((n) => (
                            <div key={n} className="h-32 animate-pulse rounded-tarjeta bg-gris-claro" />
                        ))}
                    </div>
                )}

                {serviciosQuery.isError && (
                    <p className="text-sm text-red-600">Algo falló cargando los servicios. Código REF-SRV1. Intenta de nuevo.</p>
                )}

                {serviciosQuery.data && serviciosQuery.data.length === 0 && (
                    <p className="text-sm text-texto-suave">Este negocio aún no tiene servicios disponibles.</p>
                )}

                {serviciosQuery.data && serviciosQuery.data.length > 0 && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {serviciosQuery.data.map((servicio) => (
                            <Tarjeta key={servicio.id}>
                                {servicio.foto && <img src={servicio.foto} alt="" className="mb-3 h-32 w-full rounded-boton object-cover" />}
                                <p className="font-semibold text-tinta">{servicio.nombre}</p>
                                <p className="text-sm text-texto-suave">
                                    ${servicio.precio.toLocaleString('es-CO')} · {servicio.duracionMinutos} min
                                </p>
                                <Boton className="mt-3 w-full" onClick={() => setServicioElegido(servicio)}>
                                    Agendar
                                </Boton>
                            </Tarjeta>
                        ))}
                    </div>
                )}
            </section>

            {servicioElegido && (
                <FlujoReserva
                    slug={slug}
                    direccion={direccion}
                    servicio={servicioElegido}
                    onCerrar={() => setServicioElegido(null)}
                />
            )}
        </main>
    );
}
