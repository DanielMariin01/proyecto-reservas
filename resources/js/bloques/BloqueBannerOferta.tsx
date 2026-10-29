import { ArrowRight, BadgePercent } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import ImagenConManuscrito from '@/componentes/ImagenConManuscrito';
import { obtenerEstiloGradiente } from './colorLibre';
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
        <section style={obtenerEstiloGradiente(datos)}>
            <div className="mx-auto flex max-w-[1200px] flex-col gap-6 px-4 py-6 sm:py-10 md:flex-row md:items-center md:justify-between">
                <div className="md:w-1/2">
                    {etiqueta && (
                        <p className="mb-3 inline-flex items-center gap-1.5 rounded-boton bg-black/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                            <BadgePercent size={14} strokeWidth={1.75} />
                            {etiqueta}
                        </p>
                    )}
                    {titulo && <h2 className="text-3xl font-extrabold sm:text-4xl">{titulo}</h2>}
                    {descripcion && <p className="mt-3 max-w-[450px] text-sm opacity-80 sm:text-base">{descripcion}</p>}

                    {textoBoton && (
                        <a href={enlaceBoton || undefined} target="_blank" rel="noreferrer" className="mt-5 inline-block">
                            <Boton tamano="grande" className="flex items-center gap-2">
                                {textoBoton}
                                <ArrowRight size={20} strokeWidth={1.75} />
                            </Boton>
                        </a>
                    )}
                </div>

                {imagen && <ImagenConManuscrito imagen={imagen} textoManuscrito={textoManuscrito} className="md:w-1/2" />}
            </div>
        </section>
    );
}
