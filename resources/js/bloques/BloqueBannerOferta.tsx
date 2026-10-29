import { ArrowRight, BadgePercent, Sparkles } from 'lucide-react';
import type { CSSProperties, ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import ImagenConManuscrito from '@/componentes/ImagenConManuscrito';
import { obtenerColoresLibres, obtenerEstiloGradiente } from './colorLibre';
import { obtenerIconosTema, POSICIONES_DECORATIVAS } from './temaDecorativo';
import type { PropiedadesBloque } from './tipos';

/**
 * Elementos decorativos (destellos, píldoras superpuestas) hechos solo
 * con opacidad de blanco/negro ya permitidos — nunca un color nuevo:
 * es la composición de una franja promocional de tienda online, no los
 * colores exactos de ninguna referencia. Si hay un `temaDecorativo`
 * activo (activación manual del super admin, nunca por fecha del
 * sistema), esos íconos dispersos reemplazan los destellos genéricos.
 */
export default function BloqueBannerOferta({ datos }: PropiedadesBloque): ReactElement {
    const etiqueta = typeof datos.etiqueta === 'string' ? datos.etiqueta : '';
    const titulo = typeof datos.titulo === 'string' ? datos.titulo : '';
    const descripcion = typeof datos.descripcion === 'string' ? datos.descripcion : '';
    const textoBoton = typeof datos.textoBoton === 'string' ? datos.textoBoton : '';
    const enlaceBoton = typeof datos.enlaceBoton === 'string' ? datos.enlaceBoton : '';
    const imagen = typeof datos.imagen === 'string' ? datos.imagen : null;
    const textoManuscrito = typeof datos.textoManuscrito === 'string' ? datos.textoManuscrito : '';
    const iconosTema = obtenerIconosTema(datos.temaDecorativo);
    const { colorTexto } = obtenerColoresLibres(datos);

    return (
        <section style={obtenerEstiloGradiente(datos)} className="relative overflow-hidden">
            <div className="pointer-events-none absolute inset-0">
                {iconosTema ? (
                    POSICIONES_DECORATIVAS.map((posicion, indice) => {
                        const Icono = iconosTema[indice % iconosTema.length];
                        // Siluetas rellenas (no solo trazo) en el color de
                        // texto del bloque (nunca blanco fijo): así se ven
                        // sin importar si colorFondo es claro u oscuro,
                        // configurado o no — un trazo delgado al 20% de
                        // opacidad se vuelve invisible a estos tamaños.
                        const estilo: CSSProperties = {
                            top: posicion.top,
                            left: posicion.left,
                            transform: `rotate(${posicion.rotate}deg)`,
                            color: colorTexto,
                            opacity: 0.3,
                        };

                        return <Icono key={indice} size={posicion.size} fill="currentColor" strokeWidth={1} className="absolute" style={estilo} />;
                    })
                ) : (
                    <>
                        <div className="absolute -left-6 -top-10 h-28 w-28 rounded-full bg-blanco/10" />
                        <div className="absolute -bottom-10 left-1/3 h-20 w-20 rounded-full bg-blanco/10" />
                        <Sparkles size={22} strokeWidth={1.5} className="absolute right-[18%] top-3 opacity-40" />
                        <Sparkles size={14} strokeWidth={1.5} className="absolute bottom-3 right-[8%] opacity-30" />
                    </>
                )}
            </div>

            <div className="relative mx-auto flex max-w-[1200px] flex-col gap-4 px-4 py-4 sm:py-6 md:flex-row md:items-center md:justify-between">
                <div className="md:w-1/2">
                    {etiqueta && (
                        <p className="mb-2 inline-flex items-center gap-2 rounded-boton bg-black/15 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest">
                            <BadgePercent size={22} strokeWidth={1.75} />
                            {etiqueta}
                        </p>
                    )}
                    {titulo && <h2 className="text-2xl font-extrabold sm:text-3xl">{titulo}</h2>}
                    {descripcion && <p className="mt-2 max-w-[450px] text-sm opacity-80 sm:text-base">{descripcion}</p>}

                    {textoBoton && (
                        <a href={enlaceBoton || undefined} target="_blank" rel="noreferrer" className="mt-4 inline-block">
                            <Boton tamano="grande" className="flex items-center gap-2">
                                {textoBoton}
                                <ArrowRight size={20} strokeWidth={1.75} />
                            </Boton>
                        </a>
                    )}
                </div>

                {imagen && <ImagenConManuscrito imagen={imagen} textoManuscrito={textoManuscrito} className="md:w-1/3" />}
            </div>
        </section>
    );
}
