import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import Boton from '@/componentes/Boton';
import type { CitaConAcciones } from '../api/citaAcciones';
import { obtenerAgenda } from '../api/agenda';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import CalendarioFranjasHora from './CalendarioFranjasHora';
import ModalAccionesCita from './ModalAccionesCita';
import ModalesAccionesCita from './ModalesAccionesCita';
import SelectorProfesionalesAgenda from './SelectorProfesionalesAgenda';
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
    const [profesionalSeleccionadoId, setProfesionalSeleccionadoId] = useState<number | null>(null);
    const [citaEnMenu, setCitaEnMenu] = useState<CitaConAcciones | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-agenda', negocioSlug, fecha],
        queryFn: () => obtenerAgenda(negocioSlug, fecha),
    });

    const estadoAcciones = useAccionesCita(negocioSlug, () =>
        queryClient.invalidateQueries({ queryKey: ['panel-agenda', negocioSlug, fecha] }),
    );

    const columnas = consulta.data?.columnas ?? [];
    const columnaSeleccionada = columnas.find((columna) => columna.profesionalId === profesionalSeleccionadoId) ?? columnas[0] ?? null;

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            aria-label="Día anterior"
                            onClick={() => setFecha((actual) => sumarDias(actual, -1))}
                            className="flex h-11 w-11 items-center justify-center rounded-boton border border-gris-claro text-tinta"
                        >
                            <ChevronLeft size={18} strokeWidth={1.75} />
                        </button>
                        <span className="text-base font-semibold text-tinta capitalize">{consulta.data?.fechaEtiqueta ?? '...'}</span>
                        <button
                            type="button"
                            aria-label="Día siguiente"
                            onClick={() => setFecha((actual) => sumarDias(actual, 1))}
                            className="flex h-11 w-11 items-center justify-center rounded-boton border border-gris-claro text-tinta"
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
                ) : columnas.length === 0 ? (
                    <p className="text-sm text-texto-suave">Aún no hay profesionales activos. Crea uno en Profesionales para que aparezca aquí.</p>
                ) : (
                    <>
                        <SelectorProfesionalesAgenda
                            columnas={columnas}
                            profesionalSeleccionadoId={columnaSeleccionada?.profesionalId ?? null}
                            onSeleccionar={setProfesionalSeleccionadoId}
                        />

                        {columnaSeleccionada && (
                            <CalendarioFranjasHora
                                horaInicio={columnaSeleccionada.horaInicio}
                                horaFin={columnaSeleccionada.horaFin}
                                citas={columnaSeleccionada.citas}
                                colorProfesional={columnaSeleccionada.profesionalColor}
                                onAbrirAccion={setCitaEnMenu}
                            />
                        )}
                    </>
                )}
            </div>

            {citaEnMenu && (
                <ModalAccionesCita
                    cita={citaEnMenu}
                    onElegirAccion={(tipo) => {
                        const cita = citaEnMenu;
                        setCitaEnMenu(null);
                        estadoAcciones.abrirAccion(tipo, cita);
                    }}
                    onCerrar={() => setCitaEnMenu(null)}
                />
            )}

            <ModalesAccionesCita {...estadoAcciones} />
        </LayoutPanelNegocio>
    );
}
