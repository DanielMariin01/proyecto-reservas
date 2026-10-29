import type { ReactElement } from 'react';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

interface TarjetaDatos {
    imagen?: unknown;
    titulo?: unknown;
    descripcion?: unknown;
}

const CLASES_COLUMNAS: Record<number, string> = {
    1: 'sm:grid-cols-1',
    2: 'sm:grid-cols-2',
    3: 'sm:grid-cols-3',
};

/**
 * Bloque genérico de contenido manual (no atado a ningún modelo): sirve
 * para servicios destacados, pasos de un proceso o lo que el negocio
 * necesite. Siempre 1 columna en celular, sin importar cuántas eligió
 * el super admin para escritorio.
 */
export default function BloqueTarjetas({ datos }: PropiedadesBloque): ReactElement {
    const tarjetas = Array.isArray(datos.tarjetas) ? (datos.tarjetas as TarjetaDatos[]) : [];
    const columnas = Number(datos.columnas) || 2;

    if (tarjetas.length === 0) {
        return <></>;
    }

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-5xl px-4 py-10 sm:py-16">
                <div className={`grid grid-cols-1 gap-4 ${CLASES_COLUMNAS[columnas] ?? CLASES_COLUMNAS[2]}`}>
                    {tarjetas.map((tarjeta, indice) => {
                        const imagen = typeof tarjeta.imagen === 'string' ? tarjeta.imagen : null;
                        const titulo = typeof tarjeta.titulo === 'string' ? tarjeta.titulo : '';
                        const descripcion = typeof tarjeta.descripcion === 'string' ? tarjeta.descripcion : '';

                        return (
                            <div key={indice} className="text-center">
                                {imagen && <img src={imagen} alt="" className="mb-3 aspect-[4/3] w-full rounded-tarjeta object-cover" />}
                                {titulo && <p className="font-semibold">{titulo}</p>}
                                {descripcion && <p className="mt-1 text-sm opacity-80">{descripcion}</p>}
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
