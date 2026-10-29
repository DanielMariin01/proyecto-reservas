import { Scissors } from 'lucide-react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerServicios } from '@/flujo-reserva/api/clientePublico';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueServicios({ datos, slug, onReservar }: PropiedadesBloque): ReactElement {
    const serviciosQuery = useQuery({
        queryKey: ['servicios', slug],
        queryFn: () => obtenerServicios(slug),
    });

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-2xl font-bold sm:text-3xl">Servicios</h2>

                {serviciosQuery.isLoading && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Cargando servicios">
                        {[1, 2, 3].map((n) => (
                            <div key={n} className="h-32 animate-pulse rounded-tarjeta bg-gris-claro" />
                        ))}
                    </div>
                )}

                {serviciosQuery.isError && <p className="text-sm text-red-600">Algo falló cargando los servicios. Código REF-SRV1.</p>}

                {serviciosQuery.data && serviciosQuery.data.length === 0 && (
                    <p className="text-sm opacity-80">Este negocio aún no tiene servicios disponibles.</p>
                )}

                {serviciosQuery.data && serviciosQuery.data.length > 0 && (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {serviciosQuery.data.map((servicio) => (
                            <Tarjeta key={servicio.id}>
                                {servicio.foto && <img src={servicio.foto} alt="" className="mb-3 h-32 w-full rounded-boton object-cover" />}
                                <span className="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-fondo-panel text-primario">
                                    <Scissors size={18} strokeWidth={1.75} />
                                </span>
                                <p className="font-semibold text-tinta">{servicio.nombre}</p>
                                {servicio.descripcion && <p className="mt-1 text-sm text-texto-suave">{servicio.descripcion}</p>}
                                <p className="mt-2 text-sm text-texto-suave">
                                    ${servicio.precio.toLocaleString('es-CO')} · {servicio.duracionMinutos} min
                                </p>
                                <Boton className="mt-3 w-full" onClick={() => onReservar({ servicio })}>
                                    Agendar
                                </Boton>
                            </Tarjeta>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
