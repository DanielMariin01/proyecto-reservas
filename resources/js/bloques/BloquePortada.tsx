import { Clock, MapPin, Play } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import { obtenerCapaPortada } from './capaPortada';
import { obtenerIcono } from './iconoBloque';
import type { PropiedadesBloque } from './tipos';

interface IconoConEtiqueta {
    icono?: unknown;
    etiqueta?: unknown;
}

/**
 * Sin calificación de estrellas/reseñas: el sistema real de reseñas
 * todavía no existe (tarea aparte) y no se inventa un campo manual
 * para esto en portada — cuando exista, se agrega ahí.
 */
export default function BloquePortada({ datos, pagina, onReservar }: PropiedadesBloque): ReactElement {
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const horarioHoy = typeof datos.horarioHoy === 'string' ? datos.horarioHoy : '';
    const descripcion = typeof datos.descripcion === 'string' ? datos.descripcion : '';
    const textoBotonPrincipal = typeof datos.textoBotonPrincipal === 'string' && datos.textoBotonPrincipal !== '' ? datos.textoBotonPrincipal : 'Reservar cita';
    const mostrarBotonVideo = Boolean(datos.mostrarBotonVideo) && typeof datos.enlaceVideo === 'string' && datos.enlaceVideo !== '';
    const enlaceVideo = typeof datos.enlaceVideo === 'string' ? datos.enlaceVideo : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const textoManuscrito = typeof datos.textoManuscrito === 'string' ? datos.textoManuscrito : '';
    const iconos = Array.isArray(datos.iconos) ? (datos.iconos as IconoConEtiqueta[]) : [];
    const capa = obtenerCapaPortada(datos.overlay);

    return (
        <section className="relative overflow-hidden">
            <div
                className={`relative aspect-[4/5] min-h-[520px] w-full sm:aspect-[16/9] sm:min-h-[420px] lg:aspect-[21/9] lg:min-h-[380px] ${capa.texto}`}
            >
                {imagen ? (
                    <img src={imagen} alt="" className="absolute inset-0 h-full w-full object-cover" />
                ) : (
                    <div className="absolute inset-0 bg-tinta" />
                )}
                <div className="absolute inset-0" style={capa.capaEstilo} />

                {textoManuscrito && (
                    <span className="absolute right-6 top-6 -rotate-3 rounded-boton bg-blanco px-4 py-2 text-lg italic text-tinta shadow-tarjeta sm:text-xl">
                        {textoManuscrito}
                    </span>
                )}

                <div className="absolute inset-0 z-10 flex flex-col justify-end px-4 pb-10 pt-16 sm:pb-14">
                    <div className="mx-auto w-full max-w-[1200px]">
                        <span className={`inline-block rounded-boton px-3 py-1 text-sm font-semibold uppercase tracking-widest ${capa.pildora}`}>
                            {pagina.tipoEtiqueta}
                        </span>

                        <h1 className="mt-4 max-w-3xl text-5xl font-extrabold uppercase leading-[1.05] sm:text-6xl md:text-7xl">{titulo}</h1>

                        {(pagina.direccion || horarioHoy) && (
                            <div className="mt-5 flex flex-col gap-2 text-base opacity-90 sm:flex-row sm:items-center sm:gap-6 sm:text-lg">
                                {pagina.direccion && (
                                    <span className="flex items-center gap-2">
                                        <MapPin size={20} strokeWidth={1.75} />
                                        {pagina.direccion}
                                    </span>
                                )}
                                {horarioHoy && (
                                    <span className="flex items-center gap-2">
                                        <Clock size={20} strokeWidth={1.75} />
                                        {horarioHoy}
                                    </span>
                                )}
                            </div>
                        )}

                        {descripcion && <p className="mt-5 max-w-[500px] text-base opacity-90 sm:text-lg">{descripcion}</p>}

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
                                            <Icono size={36} strokeWidth={1.75} />
                                            <span className="text-sm opacity-90">{etiquetaIcono}</span>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}
