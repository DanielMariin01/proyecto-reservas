import type { ReactElement } from 'react';
import Insignia from '@/componentes/Insignia';
import type { CitaConAcciones } from '../api/citaAcciones';
import BotonesAccionesCita, { type AccionCita } from './BotonesAccionesCita';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface Propiedades {
    cita: CitaConAcciones;
    colorProfesional: string | null;
    onAbrirAccion: (accion: AccionCita) => void;
}

export default function TarjetaCitaAgenda({ cita, colorProfesional, onAbrirAccion }: Propiedades): ReactElement {
    return (
        <li
            className="rounded-tarjeta border-l-4 bg-blanco p-3 shadow-tarjeta"
            style={{ borderLeftColor: colorProfesional ?? '#673DE6' }}
        >
            <div className="flex items-center justify-between gap-2">
                <span className="text-sm font-semibold text-tinta">{cita.hora}</span>
                {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={cita.estadoEtiqueta} />}
            </div>

            <p className="mt-1 text-sm text-tinta">{cita.clienteNombre ?? '—'}</p>
            <p className="text-xs text-texto-suave">{cita.servicioNombre ?? '—'}</p>
            {cita.notas && <p className="mt-1 text-xs italic text-texto-suave">&ldquo;{cita.notas}&rdquo;</p>}

            <div className="mt-2">
                <BotonesAccionesCita acciones={cita.acciones} onAbrirAccion={onAbrirAccion} />
            </div>
        </li>
    );
}
