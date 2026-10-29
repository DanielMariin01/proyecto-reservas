import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import FormaOrganica from '@/componentes/FormaOrganica';
import { obtenerCombinacionColor } from './combinacionColor';
import type { PropiedadesBloque } from './tipos';

export default function BloquePortada({ datos, pagina, onReservar }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' && datos.titulo !== '' ? datos.titulo : pagina.nombreNegocio;
    const subtitulo = typeof datos.subtitulo === 'string' ? datos.subtitulo : '';
    const descripcion = typeof datos.descripcion === 'string' ? datos.descripcion : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const combinacion = obtenerCombinacionColor(datos.combinacionColor);

    return (
        <section className={`${combinacion.fondo} ${combinacion.texto}`}>
            <div className="mx-auto flex max-w-[1200px] flex-col-reverse items-center gap-10 px-4 py-16 sm:py-24 md:flex-row md:justify-between">
                <div className="max-w-xl text-center md:text-left">
                    <h1 className="text-4xl font-extrabold uppercase leading-[1.05] sm:text-5xl md:text-6xl">{titulo}</h1>

                    {subtitulo && <p className="mt-4 text-base font-medium italic text-celeste sm:text-lg">{subtitulo}</p>}

                    {descripcion && <p className="mx-auto mt-4 max-w-[450px] text-sm text-gris-claro sm:text-base md:mx-0">{descripcion}</p>}

                    <Boton className="mt-8" onClick={() => onReservar({})}>
                        Reservar
                    </Boton>
                </div>

                {imagen && (
                    <FormaOrganica id="forma-portada" className="aspect-square w-full max-w-[260px] shrink-0 sm:max-w-sm md:w-1/2 md:max-w-md">
                        <img src={imagen} alt="" className="h-full w-full object-cover" />
                    </FormaOrganica>
                )}
            </div>
        </section>
    );
}
