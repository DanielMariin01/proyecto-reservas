import { LogIn } from 'lucide-react';
import type { ReactElement } from 'react';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

interface Enlace {
    texto?: unknown;
    url?: unknown;
}

/**
 * El ícono de iniciar sesión solo enlaza a /ingresar, sin verificar si
 * ya hay una sesión activa (simple a propósito). Los enlaces de
 * navegación se ocultan en celular (sin menú hamburguesa en este
 * alcance) — logo e ícono de iniciar sesión se quedan siempre visibles.
 */
export default function BloqueEncabezado({ datos, pagina }: PropiedadesBloque): ReactElement {
    const enlaces = Array.isArray(datos.enlaces) ? (datos.enlaces as Enlace[]) : [];

    return (
        <header style={obtenerEstiloColor(datos)}>
            <div className="mx-auto flex max-w-[1200px] items-center justify-between gap-4 px-4 py-4">
                {pagina.logo ? (
                    <img src={pagina.logo} alt={pagina.nombreNegocio} className="h-10 w-auto object-contain" />
                ) : (
                    <span className="font-bold">{pagina.nombreNegocio}</span>
                )}

                {enlaces.length > 0 && (
                    <nav className="hidden items-center gap-6 text-sm font-medium md:flex">
                        {enlaces.map((enlace, indice) => {
                            const texto = typeof enlace.texto === 'string' ? enlace.texto : '';
                            const url = typeof enlace.url === 'string' ? enlace.url : '#';

                            return (
                                <a key={indice} href={url} className="hover:opacity-75">
                                    {texto}
                                </a>
                            );
                        })}
                    </nav>
                )}

                <a href="/ingresar" aria-label="Iniciar sesión" className="flex items-center">
                    <LogIn size={22} strokeWidth={1.75} />
                </a>
            </div>
        </header>
    );
}
