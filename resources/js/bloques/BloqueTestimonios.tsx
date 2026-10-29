import { Star } from 'lucide-react';
import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

interface Testimonio {
    cita?: unknown;
    nombre?: unknown;
    foto?: unknown;
    estrellas?: unknown;
}

const MAXIMO_ESTRELLAS = 5;

/**
 * RF-PAG-03: contenido manual (cita, nombre, foto y estrellas elegidos
 * a mano por el super admin), sin relación con citas reales ni con un
 * sistema de reseñas automático. `data.fuente` ya queda guardado como
 * 'manual' (App\Enums\FuenteTestimonios) pensando en que, cuando exista
 * el sistema real de reseñas, se pueda sumar una fuente 'sistema' que
 * resuelva este mismo arreglo de testimonios desde otra parte sin tener
 * que cambiar este componente.
 */
export default function BloqueTestimonios({ datos }: PropiedadesBloque): ReactElement {
    const testimonios = Array.isArray(datos.testimonios) ? (datos.testimonios as Testimonio[]) : [];

    if (testimonios.length === 0) {
        return <></>;
    }

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-16 sm:py-24">
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {testimonios.map((testimonio, indice) => {
                        const cita = typeof testimonio.cita === 'string' ? testimonio.cita : '';
                        const nombre = typeof testimonio.nombre === 'string' ? testimonio.nombre : '';
                        const foto = typeof testimonio.foto === 'string' ? testimonio.foto : null;
                        const estrellas = Number(testimonio.estrellas) || 0;

                        return (
                            <Tarjeta key={indice}>
                                <div className="mb-3 flex gap-1">
                                    {Array.from({ length: MAXIMO_ESTRELLAS }, (_valor, posicion) => (
                                        <Star
                                            key={posicion}
                                            size={16}
                                            strokeWidth={1.75}
                                            className={posicion < estrellas ? 'fill-secundario text-secundario' : 'text-gris-claro'}
                                        />
                                    ))}
                                </div>

                                {cita && <p className="text-sm text-tinta">&ldquo;{cita}&rdquo;</p>}

                                <div className="mt-4 flex items-center gap-3">
                                    {foto && <img src={foto} alt="" className="h-10 w-10 rounded-full object-cover" />}
                                    {nombre && <p className="text-sm font-semibold text-tinta">{nombre}</p>}
                                </div>
                            </Tarjeta>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
