import type { ReactElement } from 'react';
import Insignia from '@/componentes/Insignia';
import type { CitaConAcciones } from '../api/citaAcciones';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface Propiedades {
    cita: CitaConAcciones;
    colorProfesional: string | null;
    onAbrirMenu: () => void;
}

export default function TarjetaCitaAgenda({ cita, colorProfesional, onAbrirMenu }: Propiedades): ReactElement {
    return (
        <button
            type="button"
            onClick={onAbrirMenu}
            className="flex h-full w-full cursor-pointer flex-col overflow-hidden rounded-tarjeta border-l-4 bg-blanco p-2 text-left shadow-tarjeta transition-colors hover:bg-fondo-panel"
            style={{ borderLeftColor: colorProfesional ?? '#673DE6' }}
        >
            <div className="flex items-center justify-between gap-2">
                <span className="text-sm font-semibold text-tinta">{cita.hora}</span>
                {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={cita.estadoEtiqueta} />}
            </div>

            <p className="truncate text-sm text-tinta">{cita.clienteNombre ?? '—'}</p>
            <p className="truncate text-xs text-texto-suave">{cita.servicioNombre ?? '—'}</p>
        </button>
    );
}
