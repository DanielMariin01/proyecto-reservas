import type { ReactElement } from 'react';
import type { PropiedadesBloque } from './tipos';

interface Enlace {
    texto?: unknown;
    url?: unknown;
}

export default function BloquePieDePagina({ datos, pagina }: PropiedadesBloque): ReactElement {
    const descripcionCorta = typeof datos.descripcionCorta === 'string' ? datos.descripcionCorta : '';
    const enlaces = Array.isArray(datos.enlaces) ? (datos.enlaces as Enlace[]) : [];
    const anio = new Date().getFullYear();

    return (
        <footer className="bg-tinta text-blanco">
            <div className="mx-auto max-w-[1200px] px-4 py-12">
                <div className="flex flex-col gap-8 sm:flex-row sm:justify-between">
                    <div className="max-w-sm">
                        <p className="text-lg font-bold">{pagina.nombreNegocio}</p>
                        {descripcionCorta && <p className="mt-2 text-sm text-gris-claro">{descripcionCorta}</p>}
                        {pagina.direccion && <p className="mt-4 text-sm text-gris-claro">{pagina.direccion}</p>}
                        {pagina.whatsapp && <p className="text-sm text-gris-claro">{pagina.whatsapp}</p>}
                    </div>

                    {enlaces.length > 0 && (
                        <nav className="flex flex-col gap-2 text-sm text-gris-claro">
                            {enlaces.map((enlace, indice) => {
                                const texto = typeof enlace.texto === 'string' ? enlace.texto : '';
                                const url = typeof enlace.url === 'string' ? enlace.url : '#';

                                return (
                                    <a key={indice} href={url} className="hover:text-blanco">
                                        {texto}
                                    </a>
                                );
                            })}
                        </nav>
                    )}
                </div>

                <p className="mt-10 text-xs text-gris-claro">
                    © {anio} {pagina.nombreNegocio}
                </p>
            </div>
        </footer>
    );
}
