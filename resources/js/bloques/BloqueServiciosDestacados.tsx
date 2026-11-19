import { Flame, Users } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Carrusel from '@/componentes/Carrusel';
import Tarjeta from '@/componentes/Tarjeta';
import type { ServicioDestacado } from '@/tipos';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

function irAlCatalogoDeServicios(): void {
    document.getElementById('servicios')?.scrollIntoView({ behavior: 'smooth' });
}

/**
 * RF-BLQ (servicios_destacados): el ranking ya llega calculado desde
 * RecursoPaginaPublica (App\Services\ServicioPopularidadServicios) —
 * este bloque solo dibuja lo que recibe, igual que `testimonios` con
 * fuente sistema.
 */
export default function BloqueServiciosDestacados({ datos, onReservar }: PropiedadesBloque): ReactElement {
    const servicios = Array.isArray(datos.servicios) ? (datos.servicios as ServicioDestacado[]) : [];

    if (servicios.length === 0) {
        return <></>;
    }

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-10 sm:py-16">
                <div className="mb-6 flex items-center justify-between gap-4">
                    <h2 className="text-3xl font-bold sm:text-4xl">Lo más pedido</h2>
                    <button type="button" onClick={irAlCatalogoDeServicios} className="shrink-0 text-sm font-semibold text-primario hover:underline">
                        Ver todos los servicios
                    </button>
                </div>

                <Carrusel etiqueta="Lo más pedido">
                    {servicios.map((servicio) => (
                        <Tarjeta key={servicio.id} className="flex w-[280px] shrink-0 snap-start flex-row gap-3">
                            {servicio.foto ? (
                                <img src={servicio.foto} alt="" className="h-20 w-20 shrink-0 rounded-boton object-cover" />
                            ) : (
                                <span className="flex h-20 w-20 shrink-0 items-center justify-center rounded-boton bg-fondo-panel text-primario">
                                    <Flame size={24} strokeWidth={1.75} />
                                </span>
                            )}

                            <div className="flex min-w-0 flex-1 flex-col">
                                <span className="text-xs font-bold uppercase tracking-wide text-primario">N.º {servicio.puesto} en reservas</span>
                                <p className="mt-1 font-semibold text-tinta">{servicio.nombre}</p>
                                <p className="mt-1 text-sm text-texto-suave">
                                    ${servicio.precio.toLocaleString('es-CO')} · {servicio.duracionMinutos} min
                                </p>
                                {servicio.clientesDistintos > 0 && (
                                    <p className="mt-1 flex items-center gap-1 text-xs text-texto-suave">
                                        <Users size={14} strokeWidth={1.75} />
                                        {servicio.clientesDistintos === 1
                                            ? '1 cliente lo reservó'
                                            : `${servicio.clientesDistintos} clientes lo reservaron`}
                                    </p>
                                )}
                                <Boton className="mt-auto w-full" onClick={() => onReservar({ servicio })}>
                                    Agendar
                                </Boton>
                            </div>
                        </Tarjeta>
                    ))}
                </Carrusel>
            </div>
        </section>
    );
}
