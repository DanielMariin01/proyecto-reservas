import { Play } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import ImagenConManuscrito from '@/componentes/ImagenConManuscrito';
import { obtenerEstiloColor } from './colorLibre';
import { obtenerIcono } from './iconoBloque';
import type { PropiedadesBloque } from './tipos';

interface IconoConEtiqueta {
    icono?: unknown;
    etiqueta?: unknown;
}

export default function BloquePortada({ datos, onReservar }: PropiedadesBloque): ReactElement {
    const etiqueta = typeof datos.etiqueta === 'string' ? datos.etiqueta : '';
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const descripcion = typeof datos.descripcion === 'string' ? datos.descripcion : '';
    const textoBotonPrincipal = typeof datos.textoBotonPrincipal === 'string' && datos.textoBotonPrincipal !== '' ? datos.textoBotonPrincipal : 'Reservar cita';
    const mostrarBotonVideo = Boolean(datos.mostrarBotonVideo) && typeof datos.enlaceVideo === 'string' && datos.enlaceVideo !== '';
    const enlaceVideo = typeof datos.enlaceVideo === 'string' ? datos.enlaceVideo : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const textoManuscrito = typeof datos.textoManuscrito === 'string' ? datos.textoManuscrito : '';
    const iconos = Array.isArray(datos.iconos) ? (datos.iconos as IconoConEtiqueta[]) : [];

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto flex max-w-[1200px] flex-col gap-10 px-4 py-16 sm:py-24 md:flex-row md:items-center md:justify-between">
                <div className="md:w-1/2">
                    {etiqueta && <p className="mb-3 text-xs font-semibold uppercase tracking-widest opacity-80">{etiqueta}</p>}

                    <h1 className="text-4xl font-extrabold uppercase leading-[1.05] sm:text-5xl md:text-6xl">{titulo}</h1>

                    {descripcion && <p className="mt-4 max-w-[450px] text-sm opacity-80 sm:text-base">{descripcion}</p>}

                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <Boton onClick={() => onReservar({})}>{textoBotonPrincipal}</Boton>

                        {mostrarBotonVideo && (
                            <a href={enlaceVideo} target="_blank" rel="noreferrer">
                                <Boton variante="contorno" className="flex items-center gap-2">
                                    <Play size={16} strokeWidth={1.75} />
                                    Ver video
                                </Boton>
                            </a>
                        )}
                    </div>

                    {iconos.length > 0 && (
                        <div className="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-4">
                            {iconos.map((item, indice) => {
                                const Icono = obtenerIcono(item.icono);
                                const etiquetaIcono = typeof item.etiqueta === 'string' ? item.etiqueta : '';

                                return (
                                    <div key={indice} className="flex flex-col items-center gap-2 text-center">
                                        <Icono size={28} strokeWidth={1.75} />
                                        <span className="text-xs opacity-80">{etiquetaIcono}</span>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                {imagen && <ImagenConManuscrito imagen={imagen} textoManuscrito={textoManuscrito} className="md:w-1/2" />}
            </div>
        </section>
    );
}
