import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import ImagenConManuscrito from '@/componentes/ImagenConManuscrito';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

export default function BloqueBannerOferta({ datos }: PropiedadesBloque): ReactElement {
    const etiqueta = typeof datos.etiqueta === 'string' ? datos.etiqueta : '';
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const descripcion = typeof datos.descripcion === 'string' ? datos.descripcion : '';
    const textoBoton = typeof datos.textoBoton === 'string' ? datos.textoBoton : '';
    const enlaceBoton = typeof datos.enlaceBoton === 'string' ? datos.enlaceBoton : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const textoManuscrito = typeof datos.textoManuscrito === 'string' ? datos.textoManuscrito : '';

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto flex max-w-[1200px] flex-col gap-10 px-4 py-16 sm:py-24 md:flex-row md:items-center md:justify-between">
                <div className="md:w-1/2">
                    {etiqueta && <p className="mb-3 text-xs font-semibold uppercase tracking-widest opacity-80">{etiqueta}</p>}
                    {titulo && <h2 className="text-3xl font-extrabold sm:text-4xl">{titulo}</h2>}
                    {descripcion && <p className="mt-4 max-w-[450px] text-sm opacity-80 sm:text-base">{descripcion}</p>}

                    {textoBoton && (
                        <a href={enlaceBoton || undefined} target="_blank" rel="noreferrer" className="mt-6 inline-block">
                            <Boton>{textoBoton}</Boton>
                        </a>
                    )}
                </div>

                {imagen && <ImagenConManuscrito imagen={imagen} textoManuscrito={textoManuscrito} className="md:w-1/2" />}
            </div>
        </section>
    );
}
