import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import Boton from '@/componentes/Boton';
import type { CitaConAcciones } from '../api/citaAcciones';
import { obtenerMiAgenda } from '../api/miAgenda';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import CalendarioFranjasHora from './CalendarioFranjasHora';
import ModalAccionesCita from './ModalAccionesCita';
import ModalesAccionesCita from './ModalesAccionesCita';
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

/**
 * Misma agenda visual de franjas por hora que ya existe para el dueño
 * (PaginaAgendaDelDia), pero sin SelectorProfesionalesAgenda: es un solo
 * profesional fijo (el propio), nunca el de otro — la columna ya viene
 * resuelta por identidad desde el backend (MiHorarioController::agenda),
 * no por un id que esta pantalla pueda elegir.
 */
export default function PaginaMiAgenda({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [fecha, setFecha] = useState(hoyISO());
    const [citaEnMenu, setCitaEnMenu] = useState<CitaConAcciones | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-mi-agenda', negocioSlug, fecha],
        queryFn: () => obtenerMiAgenda(negocioSlug, fecha),
    });

    const estadoAcciones = useAccionesCita(negocioSlug, () =>
        queryClient.invalidateQueries({ queryKey: ['panel-mi-agenda', negocioSlug, fecha] }),
    );

    const columna = consulta.data?.columna ?? null;

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
                    <p className="text-sm text-texto-suave">Algo falló cargando tu agenda. Código REF-MAG1.</p>
                ) : (
                    columna && (
                        <CalendarioFranjasHora
                            horaInicio={columna.horaInicio}
                            horaFin={columna.horaFin}
                            citas={columna.citas}
                            colorProfesional={columna.profesionalColor}
                            onAbrirAccion={setCitaEnMenu}
                        />
                    )
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
