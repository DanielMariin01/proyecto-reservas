import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import Insignia from '@/componentes/Insignia';
import Tarjeta from '@/componentes/Tarjeta';
import { type CitaDelDia, obtenerPanelDelDia } from '../api/panelDelDia';
import LayoutPanelNegocio from '../LayoutPanelNegocio';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaPanelDelDia({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const consulta = useQuery({
        queryKey: ['panel-del-dia', negocioSlug],
        queryFn: () => obtenerPanelDelDia(negocioSlug),
    });

    const datos = consulta.data;
    const huecosConBloques = (datos?.huecosLibres ?? []).filter((columna) => columna.bloques.length > 0);

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-6">
                <h2 className="text-lg font-semibold text-tinta">Panel del día</h2>

                {consulta.isLoading ? (
                    <p className="text-sm text-texto-suave">Cargando...</p>
                ) : consulta.isError ? (
                    <p className="text-sm text-texto-suave">Algo falló cargando el panel. Código REF-PDD0.</p>
                ) : datos ? (
                    <>
                        <div className="grid gap-4 sm:grid-cols-3">
                            <Tarjeta>
                                <p className="text-sm text-texto-suave">Citas de hoy</p>
                                <p className="text-2xl font-bold text-tinta">{datos.totalCitasHoy}</p>
                            </Tarjeta>
                            <Tarjeta>
                                <p className="text-sm text-texto-suave">Próximas (aún no empiezan)</p>
                                <p className="text-2xl font-bold text-tinta">{datos.totalProximas}</p>
                            </Tarjeta>
                            <Tarjeta>
                                <p className="text-sm text-texto-suave">Ingresos registrados hoy</p>
                                <p className="text-2xl font-bold text-tinta">${datos.ingresosRegistrados.toLocaleString('es-CO')}</p>
                            </Tarjeta>
                        </div>

                        <Tarjeta>
                            <h3 className="mb-3 text-base font-semibold text-tinta">Citas de hoy</h3>

                            {datos.citas.length === 0 ? (
                                <p className="text-sm text-texto-suave">
                                    Sin citas hoy. Cuando reserven una hora para hoy, aparecerá aquí.
                                </p>
                            ) : (
                                <ul className="space-y-2">
                                    {datos.citas.map((cita: CitaDelDia) => (
                                        <li
                                            key={cita.id}
                                            className="flex items-center justify-between rounded-tarjeta border border-gris-claro p-3"
                                        >
                                            <div>
                                                <span className="text-sm font-semibold text-tinta">{cita.hora}</span>
                                                <span className="ml-2 text-sm text-tinta">{cita.clienteNombre ?? '—'}</span>
                                                <span className="ml-2 text-xs text-texto-suave">
                                                    {cita.servicioNombre ?? '—'} · {cita.profesionalNombre ?? '—'}
                                                </span>
                                            </div>
                                            {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={cita.estadoEtiqueta} />}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Tarjeta>

                        <Tarjeta>
                            <h3 className="mb-3 text-base font-semibold text-tinta">Huecos libres de hoy</h3>

                            {huecosConBloques.length === 0 ? (
                                <p className="text-sm text-texto-suave">
                                    Sin huecos libres. La agenda de hoy está llena, o no hay profesionales activos.
                                </p>
                            ) : (
                                <div className="grid gap-4 sm:grid-cols-2">
                                    {huecosConBloques.map((columna) => (
                                        <div key={columna.profesionalId}>
                                            <p className="mb-1 text-sm font-semibold text-tinta">{columna.profesionalNombre}</p>
                                            <ul className="space-y-1 text-sm text-texto-suave">
                                                {columna.bloques.map((bloque, indice) => <li key={indice}>{bloque}</li>)}
                                            </ul>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </Tarjeta>
                    </>
                ) : null}
            </div>
        </LayoutPanelNegocio>
    );
}
