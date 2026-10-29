import { Fragment } from 'react';
import type { ReactElement, ReactNode } from 'react';
import Boton from '@/componentes/Boton';
import ImagenConManuscrito from '@/componentes/ImagenConManuscrito';
import { obtenerEstiloColor } from './colorLibre';
import { obtenerIcono } from './iconoBloque';
import type { PropiedadesBloque } from './tipos';

interface Motivo {
    icono?: unknown;
    titulo?: unknown;
    descripcion?: unknown;
}

export default function BloqueTextoConImagen({ datos }: PropiedadesBloque): ReactElement {
    const etiqueta = typeof datos.etiqueta === 'string' ? datos.etiqueta : '';
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const parrafo = typeof datos.parrafo === 'string' ? datos.parrafo : '';
    const fraseResaltada = typeof datos.fraseResaltada === 'string' ? datos.fraseResaltada : '';
    const textoBoton = typeof datos.textoBoton === 'string' ? datos.textoBoton : '';
    const enlaceBoton = typeof datos.enlaceBoton === 'string' ? datos.enlaceBoton : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const textoManuscrito = typeof datos.textoManuscrito === 'string' ? datos.textoManuscrito : '';
    const motivos = Array.isArray(datos.motivos) ? (datos.motivos as Motivo[]) : [];

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto flex max-w-[1200px] flex-col gap-10 px-4 py-16 sm:py-24 md:flex-row md:items-center">
                {imagen && <ImagenConManuscrito imagen={imagen} textoManuscrito={textoManuscrito} className="md:w-1/2" />}

                <div className="md:w-1/2">
                    {etiqueta && <p className="mb-3 text-xs font-semibold uppercase tracking-widest opacity-80">{etiqueta}</p>}
                    {titulo && <h2 className="mb-4 text-2xl font-bold sm:text-3xl">{titulo}</h2>}

                    {motivos.length > 0 ? (
                        <div className="grid grid-cols-2 gap-6">
                            {motivos.map((motivo, indice) => {
                                const Icono = obtenerIcono(motivo.icono);
                                const tituloMotivo = typeof motivo.titulo === 'string' ? motivo.titulo : '';
                                const descripcionMotivo = typeof motivo.descripcion === 'string' ? motivo.descripcion : '';

                                return (
                                    <div key={indice}>
                                        <Icono size={24} strokeWidth={1.75} />
                                        {tituloMotivo && <p className="mt-2 font-semibold">{tituloMotivo}</p>}
                                        {descripcionMotivo && <p className="mt-1 text-sm opacity-80">{descripcionMotivo}</p>}
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        parrafo && <p className="whitespace-pre-line text-base sm:text-lg">{resaltarFrase(parrafo, fraseResaltada)}</p>
                    )}

                    {textoBoton && (
                        <a href={enlaceBoton || undefined} target="_blank" rel="noreferrer" className="mt-6 inline-block">
                            <Boton>{textoBoton}</Boton>
                        </a>
                    )}
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
