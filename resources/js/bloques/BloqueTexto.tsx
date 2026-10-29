import type { ReactElement } from 'react';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueTexto({ datos }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const parrafo = typeof datos.parrafo === 'string' ? datos.parrafo : '';

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-3xl px-4 py-10 text-center sm:py-16">
                {titulo && <h2 className="text-2xl font-bold sm:text-3xl">{titulo}</h2>}
                {parrafo && <p className="mt-4 whitespace-pre-line text-base">{parrafo}</p>}
            </div>
        </section>
    );
}
