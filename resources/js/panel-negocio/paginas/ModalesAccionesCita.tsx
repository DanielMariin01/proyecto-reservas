import type { ReactElement } from 'react';
import type { EstadoAccionesCita } from './useAccionesCita';
import ModalCambiarEstadoCita from './ModalCambiarEstadoCita';
import ModalCompletarConPago from './ModalCompletarConPago';
import ModalMotivo from './ModalMotivo';
import ModalReprogramarCita from './ModalReprogramarCita';

export default function ModalesAccionesCita({
    accion,
    errorGeneral,
    cerrarAccion,
    mutacionCambiarEstado,
    mutacionCompletarConPago,
    mutacionCancelar,
    mutacionReprogramar,
    mutacionAnularPago,
}: EstadoAccionesCita): ReactElement | null {
    if (!accion) {
        return null;
    }

    switch (accion.tipo) {
        case 'cambiarEstado':
            return (
                <ModalCambiarEstadoCita
                    destinos={accion.cita.acciones.destinosCambioEstado}
                    enviando={mutacionCambiarEstado.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(estado) => mutacionCambiarEstado.mutate(estado)}
                    onCerrar={cerrarAccion}
                />
            );
        case 'completarConPago':
            return (
                <ModalCompletarConPago
                    precioSugerido={accion.cita.precio}
                    enviando={mutacionCompletarConPago.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(datos) => mutacionCompletarConPago.mutate(datos)}
                    onCerrar={cerrarAccion}
                />
            );
        case 'cancelar':
            return (
                <ModalMotivo
                    titulo="Cancelar cita"
                    etiqueta="Motivo"
                    enviando={mutacionCancelar.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(motivo) => mutacionCancelar.mutate(motivo)}
                    onCerrar={cerrarAccion}
                />
            );
        case 'reprogramar':
            return (
                <ModalReprogramarCita
                    fechaActual={accion.cita.fecha}
                    horaActual={accion.cita.horaHM}
                    puedeForzar={accion.cita.acciones.puedeForzarReprogramacion}
                    enviando={mutacionReprogramar.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(datos) => mutacionReprogramar.mutate(datos)}
                    onCerrar={cerrarAccion}
                />
            );
        case 'anularPago':
            return (
                <ModalMotivo
                    titulo="Anular pago"
                    etiqueta="Motivo"
                    enviando={mutacionAnularPago.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(motivo) => mutacionAnularPago.mutate(motivo)}
                    onCerrar={cerrarAccion}
                />
            );
    }
}
