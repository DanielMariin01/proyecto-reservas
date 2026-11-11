import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import type { Profesional } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import SeccionBloqueos from './SeccionBloqueos';
import SeccionServiciosVinculados from './SeccionServiciosVinculados';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    profesional: Profesional;
}

export default function PaginaVerProfesional({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, profesional }: Propiedades): ReactElement {
    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">{profesional.nombre}</h2>
                    <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/profesionales/${profesional.id}/edit`)}>Editar</Boton>
                </div>

                <Tarjeta className="grid gap-4 sm:grid-cols-2">
                    <div className="flex items-center gap-4">
                        {profesional.foto ? (
                            <img src={profesional.foto} alt="" className="h-16 w-16 rounded-full object-cover" />
                        ) : (
                            <div className="h-16 w-16 rounded-full bg-gris-claro" />
                        )}
                        <div>
                            <p className="font-semibold text-tinta">{profesional.nombre}</p>
                            <p className="text-sm text-texto-suave">{profesional.especialidad ?? '—'}</p>
                        </div>
                    </div>
                    <div>
                        <p className="text-sm font-medium text-tinta">{profesional.activo ? 'Activo' : 'Inactivo'}</p>
                        <p className="text-sm text-texto-suave">Usuario vinculado: {profesional.usuarioEmail ?? 'Sin vincular'}</p>
                    </div>
                    {profesional.biografia && (
                        <div className="sm:col-span-2">
                            <p className="text-sm font-medium text-tinta">Biografía</p>
                            <p className="text-sm text-texto-suave">{profesional.biografia}</p>
                        </div>
                    )}
                    {profesional.certificaciones && (
                        <div className="sm:col-span-2">
                            <p className="text-sm font-medium text-tinta">Certificaciones</p>
                            <p className="text-sm text-texto-suave">{profesional.certificaciones}</p>
                        </div>
                    )}
                </Tarjeta>

                <SeccionServiciosVinculados slug={negocioSlug} profesionalId={profesional.id} />
                <SeccionBloqueos slug={negocioSlug} profesionalId={profesional.id} />
            </div>
        </LayoutPanelNegocio>
    );
}
