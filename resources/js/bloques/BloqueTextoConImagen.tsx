import { Fragment } from 'react';
import type { ReactElement, ReactNode } from 'react';
import { obtenerCombinacionColor } from './combinacionColor';
import type { PropiedadesBloque } from './tipos';

export default function BloqueTextoConImagen({ datos }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const parrafo = typeof datos.parrafo === 'string' ? datos.parrafo : '';
    const fraseResaltada = typeof datos.fraseResaltada === 'string' ? datos.fraseResaltada : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const combinacion = obtenerCombinacionColor(datos.combinacionColor);

    return (
        <section className={`${combinacion.fondo} ${combinacion.texto}`}>
            <div className="mx-auto max-w-[1200px] px-4 py-16 sm:py-24">
                {titulo && <h2 className="mb-10 text-center text-2xl font-bold text-acento sm:text-3xl">{titulo}</h2>}

                <div className="flex flex-col-reverse items-center gap-10 md:flex-row">
                    {parrafo && (
                        <p className="whitespace-pre-line text-left text-base sm:text-lg md:w-1/2">
                            {resaltarFrase(parrafo, fraseResaltada)}
                        </p>
                    )}

                    {imagen && <img src={imagen} alt="" className="w-full max-w-md rounded-tarjeta object-cover shadow-tarjeta md:w-1/2" />}
                </div>
            </div>
        </section>
    );
}

/**
 * Resalta la frase clave en --primario (no --acento: a este tamaño de
 * párrafo, --acento no cumple el contraste AA de la skill). Si la frase
 * no aparece tal cual en el párrafo, no se resalta nada.
 */
function resaltarFrase(parrafo: string, frase: string): ReactNode {
    if (frase === '') {
        return parrafo;
    }

    const partes = parrafo.split(frase);

    if (partes.length === 1) {
        return parrafo;
    }

    return partes.map((parte, indice) => (
        <Fragment key={indice}>
            {parte}
            {indice < partes.length - 1 && <strong className="font-semibold text-primario">{frase}</strong>}
        </Fragment>
    ));
}
