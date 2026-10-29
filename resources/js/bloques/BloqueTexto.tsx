import type { ReactElement } from 'react';
import { obtenerCombinacionColor } from './combinacionColor';
import type { PropiedadesBloque } from './tipos';

export default function BloqueTexto({ datos }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const parrafo = typeof datos.parrafo === 'string' ? datos.parrafo : '';
    const combinacion = obtenerCombinacionColor(datos.combinacionColor);

    return (
        <section className={`${combinacion.fondo} ${combinacion.texto}`}>
            <div className="mx-auto max-w-3xl px-4 py-10 sm:py-16">
                {titulo && <h2 className="mb-4 text-2xl font-bold sm:text-3xl">{titulo}</h2>}
                {parrafo && <p className="whitespace-pre-line text-base">{parrafo}</p>}
            </div>
        </section>
    );
}
