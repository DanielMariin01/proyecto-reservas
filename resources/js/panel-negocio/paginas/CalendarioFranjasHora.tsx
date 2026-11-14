import type { ReactElement } from 'react';
import type { CitaConAcciones } from '../api/citaAcciones';
import TarjetaCitaAgenda from './TarjetaCitaAgenda';

const ALTO_POR_HORA_PX = 64;

interface Propiedades {
    horaInicio: string | null;
    horaFin: string | null;
    citas: CitaConAcciones[];
    colorProfesional: string | null;
    onAbrirAccion: (cita: CitaConAcciones) => void;
}

function minutosDesdeMedianoche(horaHM: string): number {
    const [horas, minutos] = horaHM.split(':').map(Number);

    return horas * 60 + minutos;
}

function formatearEtiquetaHora(minutos: number): string {
    const horas24 = Math.floor(minutos / 60);
    const periodo = horas24 < 12 ? 'a. m.' : 'p. m.';
    const horas12 = horas24 % 12 === 0 ? 12 : horas24 % 12;

    return `${horas12}:00 ${periodo}`;
}

export default function CalendarioFranjasHora({ horaInicio, horaFin, citas, colorProfesional, onAbrirAccion }: Propiedades): ReactElement {
    if (horaInicio === null || horaFin === null) {
        return <p className="text-sm text-texto-suave">No trabaja este día.</p>;
    }

    const minutosInicioTrabajo = minutosDesdeMedianoche(horaInicio);
    const minutosFinTrabajo = minutosDesdeMedianoche(horaFin);

    const minutosCitas = citas.flatMap((cita) => [minutosDesdeMedianoche(cita.horaHM), minutosDesdeMedianoche(cita.finHM)]);
    const minutosInicioGrilla = Math.min(minutosInicioTrabajo, ...minutosCitas);
    const minutosFinGrilla = Math.max(minutosFinTrabajo, ...minutosCitas);

    const horaInicioGrilla = Math.floor(minutosInicioGrilla / 60) * 60;
    const horaFinGrilla = Math.ceil(minutosFinGrilla / 60) * 60;
    const totalHoras = Math.max(1, (horaFinGrilla - horaInicioGrilla) / 60);
    const marcasDeHora = Array.from({ length: totalHoras + 1 }, (_, indice) => horaInicioGrilla + indice * 60);

    return (
        <div className="relative" style={{ height: totalHoras * ALTO_POR_HORA_PX }}>
            {marcasDeHora.map((minutos) => (
                <div
                    key={minutos}
                    className="absolute left-0 right-0 flex items-start gap-2 border-t border-gris-claro"
                    style={{ top: ((minutos - horaInicioGrilla) / 60) * ALTO_POR_HORA_PX }}
                >
                    <span className="-mt-2 w-16 shrink-0 bg-fondo-panel pr-2 text-xs text-texto-suave">{formatearEtiquetaHora(minutos)}</span>
                </div>
            ))}

            <div className="absolute left-16 right-0 top-0">
                {citas.map((cita) => {
                    const inicioMinutos = minutosDesdeMedianoche(cita.horaHM);
                    const finMinutos = minutosDesdeMedianoche(cita.finHM);
                    const top = ((inicioMinutos - horaInicioGrilla) / 60) * ALTO_POR_HORA_PX;
                    const alto = Math.max(((finMinutos - inicioMinutos) / 60) * ALTO_POR_HORA_PX, 40);

                    return (
                        <div key={cita.id} className="absolute left-0 right-1" style={{ top, height: alto }}>
                            <TarjetaCitaAgenda cita={cita} colorProfesional={colorProfesional} onAbrirMenu={() => onAbrirAccion(cita)} />
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
