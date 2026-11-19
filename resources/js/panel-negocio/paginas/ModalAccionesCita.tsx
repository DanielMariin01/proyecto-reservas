import type { ReactElement } from 'react';
import Insignia from '@/componentes/Insignia';
import Modal from '@/componentes/Modal';
import type { CitaConAcciones } from '../api/citaAcciones';
import BotonesAccionesCita, { type AccionCita } from './BotonesAccionesCita';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface Propiedades {
    cita: CitaConAcciones;
    onElegirAccion: (accion: AccionCita) => void;
    onCerrar: () => void;
}

/**
 * T31: la agenda del día deja de mostrar las acciones de una cita
 * directo en la tarjeta (sección 7 de la skill diseno-reservas, antes
 * "sin menús ocultos") — ahora la tarjeta abre este modal, y elegir una
 * acción aquí abre el modal específico de siempre (ModalCambiarEstadoCita,
 * etc., ver ModalesAccionesCita). La lista general de citas no cambia:
 * sigue mostrando BotonesAccionesCita directo en la fila.
 */
export default function ModalAccionesCita({ cita, onElegirAccion, onCerrar }: Propiedades): ReactElement {
    return (
        <Modal titulo="Acciones de la cita" onCerrar={onCerrar}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-2">
                    <div>
                        <p className="text-sm font-semibold text-tinta">
                            {cita.hora} · {cita.clienteNombre ?? '—'}
                        </p>
                        <p className="text-sm text-texto-suave">{cita.servicioNombre ?? '—'}</p>
                        {cita.notas && <p className="mt-1 text-xs italic text-texto-suave">&ldquo;{cita.notas}&rdquo;</p>}
                    </div>
                    {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={cita.estadoEtiqueta} />}
                </div>

                <BotonesAccionesCita acciones={cita.acciones} onAbrirAccion={onElegirAccion} />
            </div>
        </Modal>
    );
}
