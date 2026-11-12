import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Insignia from '@/componentes/Insignia';
import type { CitaAgenda } from '../api/agenda';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

export type AccionCita = 'cambiarEstado' | 'completarConPago' | 'cancelar' | 'reprogramar' | 'anularPago';

interface Propiedades {
    cita: CitaAgenda;
    colorProfesional: string | null;
    onAbrirAccion: (accion: AccionCita) => void;
}

export default function TarjetaCitaAgenda({ cita, colorProfesional, onAbrirAccion }: Propiedades): ReactElement {
    const { acciones } = cita;

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

            {(acciones.puedeCompletarConPago ||
                acciones.puedeCambiarEstado ||
                acciones.puedeReprogramar ||
                acciones.puedeCancelar ||
                acciones.puedeAnularPago) && (
                <div className="mt-2 flex flex-wrap gap-1">
                    {acciones.puedeCompletarConPago && (
                        <Boton variante="texto" onClick={() => onAbrirAccion('completarConPago')}>
                            Completar y pagar
                        </Boton>
                    )}
                    {acciones.puedeCambiarEstado && (
                        <Boton variante="texto" onClick={() => onAbrirAccion('cambiarEstado')}>
                            Cambiar estado
                        </Boton>
                    )}
                    {acciones.puedeReprogramar && (
                        <Boton variante="texto" onClick={() => onAbrirAccion('reprogramar')}>
                            Reprogramar
                        </Boton>
                    )}
                    {acciones.puedeCancelar && (
                        <Boton variante="texto" onClick={() => onAbrirAccion('cancelar')}>
                            Cancelar
                        </Boton>
                    )}
                    {acciones.puedeAnularPago && (
                        <Boton variante="texto" onClick={() => onAbrirAccion('anularPago')}>
                            Anular pago
                        </Boton>
                    )}
                </div>
            )}
        </li>
    );
}
