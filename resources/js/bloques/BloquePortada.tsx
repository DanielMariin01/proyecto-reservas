import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import type { PropiedadesBloque } from './tipos';

export default function BloquePortada({ datos, pagina, onReservar }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' && datos.titulo !== '' ? datos.titulo : pagina.nombreNegocio;
    const subtitulo = typeof datos.subtitulo === 'string' ? datos.subtitulo : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;

    return (
        <section className="relative flex min-h-[70vh] items-center justify-center overflow-hidden text-center">
            {imagen ? (
                <img src={imagen} alt="" className="absolute inset-0 h-full w-full object-cover" />
            ) : (
                <div className="absolute inset-0 bg-tinta" />
            )}
            <div className="absolute inset-0 bg-black/45" />

            <div className="relative z-10 px-4">
                <h1 className="text-3xl font-bold uppercase text-blanco sm:text-4xl">{titulo}</h1>
                {subtitulo && <p className="mt-3 text-lg text-blanco">{subtitulo}</p>}
                <Boton className="mt-6" onClick={() => onReservar({})}>
                    Reservar
                </Boton>
            </div>
        </section>
    );
}
