import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import type { AccionesCita } from '../api/citaAcciones';

export type AccionCita = 'cambiarEstado' | 'completarConPago' | 'cancelar' | 'reprogramar' | 'anularPago';

interface Propiedades {
    acciones: AccionesCita;
    onAbrirAccion: (accion: AccionCita) => void;
}

/**
 * Los botones de acción disponibles para una cita (completar con pago,
 * cambiar estado, reprogramar, cancelar, anular pago), compartidos entre
 * la tarjeta de la agenda y la fila de la lista general de citas. Sin
 * menú oculto (sección 7 de la skill diseno-reservas): cada botón que
 * aplica se muestra directamente.
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
    );
}
