import { Search } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import type { PropiedadesBloque } from './tipos';

interface Enlace {
    texto?: unknown;
    url?: unknown;
}

/**
 * El ícono de búsqueda es decorativo (no hay buscador funcional en el
 * MVP) y los enlaces de navegación se ocultan en celular (sin menú
 * hamburguesa en este alcance) — logo y botón "Reservar" se quedan
 * siempre visibles.
 */
export default function BloqueEncabezado({ datos, pagina, onReservar }: PropiedadesBloque): ReactElement {
    const enlaces = Array.isArray(datos.enlaces) ? (datos.enlaces as Enlace[]) : [];

    return (
        <header className="border-b border-gris-claro bg-blanco">
            <div className="mx-auto flex max-w-[1200px] items-center justify-between gap-4 px-4 py-4">
                {pagina.logo ? (
                    <img src={pagina.logo} alt={pagina.nombreNegocio} className="h-10 w-auto object-contain" />
                ) : (
                    <span className="font-bold text-tinta">{pagina.nombreNegocio}</span>
                )}

                {enlaces.length > 0 && (
                    <nav className="hidden items-center gap-6 text-sm font-medium text-tinta md:flex">
                        {enlaces.map((enlace, indice) => {
                            const texto = typeof enlace.texto === 'string' ? enlace.texto : '';
                            const url = typeof enlace.url === 'string' ? enlace.url : '#';

                            return (
                                <a key={indice} href={url} className="hover:text-primario">
                                    {texto}
                                </a>
                            );
                        })}
                    </nav>
                )}

                <div className="flex items-center gap-4">
                    <Search size={18} className="hidden text-texto-suave sm:block" strokeWidth={1.75} aria-hidden="true" />
                    <Boton onClick={() => onReservar({})}>Reservar</Boton>
                </div>
            </div>
        </header>
    );
}
