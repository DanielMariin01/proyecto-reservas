import type { ReactElement } from 'react';
import type { PropiedadesBloque } from './tipos';

export default function BloqueTexto({ datos }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const parrafo = typeof datos.parrafo === 'string' ? datos.parrafo : '';

    return (
        <section className="mx-auto max-w-3xl px-4 py-10 sm:py-16">
            {titulo && <h2 className="mb-4 text-2xl font-bold text-tinta sm:text-3xl">{titulo}</h2>}
            {parrafo && <p className="whitespace-pre-line text-base text-tinta">{parrafo}</p>}
        </section>
    );
}
