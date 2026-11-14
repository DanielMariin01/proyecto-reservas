import type { ReactElement } from 'react';
import { Banknote, CalendarClock, CheckCircle, RotateCcw, XCircle } from 'lucide-react';
import Boton from '@/componentes/Boton';
import type { AccionesCita } from '../api/citaAcciones';

export type AccionCita = 'cambiarEstado' | 'completarConPago' | 'cancelar' | 'reprogramar' | 'anularPago';

interface Propiedades {
    acciones: AccionesCita;
    onAbrirAccion: (accion: AccionCita) => void;
}

/**
 * Los botones de acción disponibles para una cita (completar con pago,
 * cambiar estado, reprogramar, cancelar, anular pago). Compartidos entre
 * la lista general de citas (visibles directo en la fila de la tabla) y
 * la agenda del día, donde desde T31 viven dentro del modal que abre la
 * tarjeta al hacer clic (ver ModalAccionesCita) en vez de ir sueltos en
 * la tarjeta.
 */
export default function BotonesAccionesCita({ acciones, onAbrirAccion }: Propiedades): ReactElement | null {
    if (
        !acciones.puedeCompletarConPago &&
        !acciones.puedeCambiarEstado &&
        !acciones.puedeReprogramar &&
        !acciones.puedeCancelar &&
        !acciones.puedeAnularPago
    ) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-1">
            {acciones.puedeCompletarConPago && (
                <Boton variante="texto" onClick={() => onAbrirAccion('completarConPago')} className="flex items-center gap-2">
                    <Banknote size={16} strokeWidth={1.75} />
                    Completar y pagar
                </Boton>
            )}
            {acciones.puedeCambiarEstado && (
                <Boton variante="texto" onClick={() => onAbrirAccion('cambiarEstado')} className="flex items-center gap-2">
                    <CheckCircle size={16} strokeWidth={1.75} />
                    Cambiar estado
                </Boton>
            )}
            {acciones.puedeReprogramar && (
                <Boton variante="texto" onClick={() => onAbrirAccion('reprogramar')} className="flex items-center gap-2">
                    <CalendarClock size={16} strokeWidth={1.75} />
                    Reprogramar
                </Boton>
            )}
            {acciones.puedeCancelar && (
                <Boton variante="texto" onClick={() => onAbrirAccion('cancelar')} className="flex items-center gap-2">
                    <XCircle size={16} strokeWidth={1.75} />
                    Cancelar
                </Boton>
            )}
            {acciones.puedeAnularPago && (
                <Boton variante="texto" onClick={() => onAbrirAccion('anularPago')} className="flex items-center gap-2">
                    <RotateCcw size={16} strokeWidth={1.75} />
                    Anular pago
                </Boton>
            )}
        </div>
    );
}
