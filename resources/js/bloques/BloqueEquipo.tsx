import { Star, Users } from 'lucide-react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerTodosLosProfesionales } from '@/flujo-reserva/api/clientePublico';
import Boton from '@/componentes/Boton';
import Carrusel from '@/componentes/Carrusel';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueEquipo({ datos, slug, onReservar }: PropiedadesBloque): ReactElement {
    const profesionalesQuery = useQuery({
        queryKey: ['todos-los-profesionales', slug],
        queryFn: () => obtenerTodosLosProfesionales(slug),
    });

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-[1200px] px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-3xl font-bold sm:text-4xl">Nuestro equipo</h2>

                {profesionalesQuery.isLoading && (
                    <div className="grid gap-4 sm:grid-cols-3" aria-label="Cargando equipo">
                        {[1, 2, 3].map((n) => (
                            <div key={n} className="h-48 animate-pulse rounded-tarjeta bg-gris-claro" />
                        ))}
                    </div>
                )}

                {profesionalesQuery.isError && <p className="text-sm text-red-600">Algo falló cargando el equipo. Código REF-EQP1.</p>}

                {profesionalesQuery.data && profesionalesQuery.data.length === 0 && (
                    <p className="text-sm opacity-80">Este negocio aún no tiene profesionales activos.</p>
                )}

                {profesionalesQuery.data && profesionalesQuery.data.length > 0 && (
                    <Carrusel etiqueta="Nuestro equipo">
                        {profesionalesQuery.data.map((profesional) => (
                            <Tarjeta key={profesional.id} className="flex w-[240px] shrink-0 snap-start flex-col items-center gap-2 text-center">
                                {profesional.foto ? (
                                    <img src={profesional.foto} alt="" className="h-20 w-20 rounded-full object-cover" />
                                ) : (
                                    <span className="flex h-20 w-20 items-center justify-center rounded-full bg-fondo-panel text-texto-suave">
                                        <Users size={28} strokeWidth={1.75} />
                                    </span>
                                )}
                                <p className="font-semibold text-tinta">{profesional.nombre}</p>
                                {profesional.especialidad && <p className="text-sm text-texto-suave">{profesional.especialidad}</p>}
                                {profesional.notaPromedio !== null && (
                                    <div className="flex items-center gap-1">
                                        <Star size={16} strokeWidth={1.75} className="fill-secundario text-secundario" />
                                        <span className="text-sm font-semibold text-tinta">{profesional.notaPromedio.toFixed(1)}</span>
                                    </div>
                                )}
                                <Boton
                                    variante="contorno"
                                    className="mt-2 w-full"
                                    onClick={() => onReservar({ profesionalId: profesional.id })}
                                >
                                    Reservar con {profesional.nombre}
                                </Boton>
                            </Tarjeta>
                        ))}
                    </Carrusel>
                )}
            </div>
        </section>
    );
}
