import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ReactElement, ReactNode } from 'react';

interface PropiedadesCarrusel {
    children: ReactNode;
    etiqueta: string;
}

const DESPLAZAMIENTO_PX = 280;

/**
 * Carrusel horizontal genérico: scroll nativo con scroll-snap (sin
 * librería nueva) y flechas que desplazan el contenedor. Cada hijo define
 * su propio ancho de slide (ej. `w-[200px] shrink-0`); este componente
 * solo se encarga del desplazamiento.
 *
 * Cuando los slides caben completos en el ancho disponible (pocos
 * profesionales/productos en una pantalla ancha) no hay nada que
 * desplazar: las flechas se ocultan y los slides se centran, en vez de
 * quedar pegados a la izquierda con la mitad derecha del bloque vacía.
 */
export default function Carrusel({ children, etiqueta }: PropiedadesCarrusel): ReactElement {
    const contenedorRef = useRef<HTMLDivElement>(null);
    const [desborda, setDesborda] = useState(false);

    useEffect(() => {
        const contenedor = contenedorRef.current;

        if (!contenedor) {
            return;
        }

        const elemento = contenedor;

        function recalcular(): void {
            setDesborda(elemento.scrollWidth > elemento.clientWidth + 1);
        }

        recalcular();

        const observador = new ResizeObserver(recalcular);
        observador.observe(elemento);

        return () => observador.disconnect();
    }, [children]);

    function desplazar(direccion: 1 | -1): void {
        contenedorRef.current?.scrollBy({ left: direccion * DESPLAZAMIENTO_PX, behavior: 'smooth' });
    }

    return (
        <div className="relative">
            <div
                ref={contenedorRef}
                aria-label={etiqueta}
                className={`flex snap-x gap-4 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden ${
                    desborda ? '' : 'justify-center'
                }`}
            >
                {children}
            </div>

            {desborda && (
                <>
                    <button
                        type="button"
                        onClick={() => desplazar(-1)}
                        aria-label="Anterior"
                        className="absolute -left-4 top-1/2 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-gris-claro bg-blanco shadow-tarjeta sm:flex"
                    >
                        <ChevronLeft size={20} strokeWidth={1.75} className="text-tinta" />
                    </button>
                    <button
                        type="button"
                        onClick={() => desplazar(1)}
                        aria-label="Siguiente"
                        className="absolute -right-4 top-1/2 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-gris-claro bg-blanco shadow-tarjeta sm:flex"
                    >
                        <ChevronRight size={20} strokeWidth={1.75} className="text-tinta" />
                    </button>
                </>
            )}
        </div>
    );
}
