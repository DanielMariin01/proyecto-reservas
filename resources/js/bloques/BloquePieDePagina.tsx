import { MapPin, Phone } from 'lucide-react';
import type { ReactElement } from 'react';
import IconoFacebook from '@/componentes/IconoFacebook';
import IconoInstagram from '@/componentes/IconoInstagram';
import type { PropiedadesBloque } from './tipos';

export default function BloquePieDePagina({ datos, pagina }: PropiedadesBloque): ReactElement {
    const facebookUrl = typeof datos.facebookUrl === 'string' ? datos.facebookUrl : '';
    const instagramUrl = typeof datos.instagramUrl === 'string' ? datos.instagramUrl : '';
    const anio = new Date().getFullYear();
    const sinRedes = facebookUrl === '' && instagramUrl === '';

    return (
        <footer className="bg-tinta text-blanco">
            <div className="mx-auto max-w-[1200px] px-4 py-12">
                <div className="grid grid-cols-1 gap-8 text-center sm:grid-cols-3">
                    <div>
                        <p className="text-lg font-bold">{pagina.nombreNegocio}</p>
                        {pagina.direccion && <p className="mt-2 text-sm text-gris-claro">{pagina.direccion}</p>}
                    </div>

                    <div>
                        <p className="text-sm font-semibold uppercase tracking-widest">Info</p>
                        {pagina.direccion && (
                            <p className="mt-3 flex items-center justify-center gap-2 text-sm text-gris-claro">
                                <MapPin size={16} strokeWidth={1.75} />
                                {pagina.direccion}
                            </p>
                        )}
                        {pagina.whatsapp && (
                            <p className="mt-2 flex items-center justify-center gap-2 text-sm text-gris-claro">
                                <Phone size={16} strokeWidth={1.75} />
                                {pagina.whatsapp}
                            </p>
                        )}
                    </div>

                    {!sinRedes && (
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-widest">Síguenos</p>
                            <div className="mt-3 flex items-center justify-center gap-4">
                                {facebookUrl && (
                                    <a href={facebookUrl} target="_blank" rel="noreferrer" aria-label="Facebook" className="hover:opacity-75">
                                        <IconoFacebook size={22} />
                                    </a>
                                )}
                                {instagramUrl && (
                                    <a href={instagramUrl} target="_blank" rel="noreferrer" aria-label="Instagram" className="hover:opacity-75">
                                        <IconoInstagram size={22} />
                                    </a>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                <p className="mt-10 text-center text-xs text-gris-claro">
                    © {anio} {pagina.nombreNegocio}
                </p>
            </div>
        </footer>
    );
}
