import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import Boton from '@/componentes/Boton';
import { obtenerAgenda } from '../api/agenda';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalesAccionesCita from './ModalesAccionesCita';
import TarjetaCitaAgenda from './TarjetaCitaAgenda';
import { useAccionesCita } from './useAccionesCita';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

function hoyISO(): string {
    return new Date().toISOString().slice(0, 10);
}

function sumarDias(fechaISO: string, dias: number): string {
    const fecha = new Date(`${fechaISO}T00:00:00`);
    fecha.setDate(fecha.getDate() + dias);

    return fecha.toISOString().slice(0, 10);
}

export default function PaginaAgendaDelDia({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [fecha, setFecha] = useState(hoyISO());
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-agenda', negocioSlug, fecha],
        queryFn: () => obtenerAgenda(negocioSlug, fecha),
    });

    const estadoAcciones = useAccionesCita(negocioSlug, () =>
        queryClient.invalidateQueries({ queryKey: ['panel-agenda', negocioSlug, fecha] }),
    );

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            aria-label="Día anterior"
                            onClick={() => setFecha((actual) => sumarDias(actual, -1))}
                            className="flex h-11 w-11 items-center justify-center rounded-boton border border-gris-claro text-tinta transition-colors hover:bg-fondo-panel"
                        >
                            <ChevronLeft size={18} strokeWidth={1.75} />
                        </button>
                        <span className="text-base font-semibold text-tinta capitalize">{consulta.data?.fechaEtiqueta ?? '...'}</span>
                        <button
                            type="button"
                            aria-label="Día siguiente"
                            onClick={() => setFecha((actual) => sumarDias(actual, 1))}
                            className="flex h-11 w-11 items-center justify-center rounded-boton border border-gris-claro text-tinta transition-colors hover:bg-fondo-panel"
                        >
                            <ChevronRight size={18} strokeWidth={1.75} />
                        </button>
                    </div>

                    <Boton variante="contorno" onClick={() => setFecha(hoyISO())}>
                        Hoy
                    </Boton>
                </div>

                {consulta.isLoading ? (
                    <p className="text-sm text-texto-suave">Cargando...</p>
                ) : consulta.isError ? (
                    <p className="text-sm text-texto-suave">Algo falló cargando la agenda. Código REF-AGN1.</p>
                ) : (consulta.data?.columnas.length ?? 0) === 0 ? (
                    <p className="text-sm text-texto-suave">Aún no hay profesionales activos. Crea uno en Profesionales para que aparezca aquí.</p>
                ) : (
                    <div className="grid gap-4 overflow-x-auto" style={{ gridTemplateColumns: `repeat(${consulta.data!.columnas.length}, minmax(240px, 1fr))` }}>
                        {consulta.data!.columnas.map((columna) => (
                            <div key={columna.profesionalId}>
                                <div className="mb-2 flex items-center gap-2">
                                    <span
                                        className="inline-block h-3 w-3 rounded-full"
                                        style={{ backgroundColor: columna.profesionalColor ?? '#673DE6' }}
                                    />
                                    <span className="text-sm font-semibold text-tinta">{columna.profesionalNombre}</span>
                                </div>

                                {columna.citas.length === 0 ? (
                                    <p className="text-sm text-texto-suave">Sin citas este día.</p>
                                ) : (
                                    <ul className="space-y-2">
                                        {columna.citas.map((cita) => (
                                            <TarjetaCitaAgenda
                                                key={cita.id}
                                                cita={cita}
                                                colorProfesional={columna.profesionalColor}
                                                onAbrirAccion={(tipo) => estadoAcciones.abrirAccion(tipo, cita)}
                                            />
                                        ))}
                                    </ul>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>

            <ModalesAccionesCita {...estadoAcciones} />
        </LayoutPanelNegocio>
    );
}
