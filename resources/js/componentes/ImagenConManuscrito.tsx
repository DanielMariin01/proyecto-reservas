import type { ReactElement } from 'react';

interface PropiedadesImagenConManuscrito {
    imagen: string;
    textoManuscrito?: string;
    className?: string;
}

/**
 * Foto rectangular con esquinas redondeadas y, opcionalmente, un texto
 * decorativo superpuesto en una esquina — en Asap itálica (sin fuente
 * nueva), no en una fuente script de Google Fonts.
 */
export default function ImagenConManuscrito({ imagen, textoManuscrito, className }: PropiedadesImagenConManuscrito): ReactElement {
    return (
        <div className={`relative w-full ${className ?? ''}`}>
            <img src={imagen} alt="" className="aspect-[4/3] w-full rounded-tarjeta object-cover shadow-tarjeta" />

            {textoManuscrito && (
                <span className="absolute -bottom-4 -right-2 -rotate-3 rounded-boton bg-blanco px-4 py-2 text-lg italic text-tinta shadow-tarjeta sm:text-xl">
                    {textoManuscrito}
                </span>
            )}
        </div>
    );
}
