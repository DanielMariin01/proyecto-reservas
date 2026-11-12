import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import Modal from '@/componentes/Modal';

interface Propiedades {
    fechaActual: string;
    horaActual: string;
    puedeForzar: boolean;
    enviando: boolean;
    errorGeneral: string | null;
    onConfirmar: (datos: { fecha: string; hora: string; forzar: boolean }) => void;
    onCerrar: () => void;
}

export default function ModalReprogramarCita({
    fechaActual,
    horaActual,
    puedeForzar,
    enviando,
    errorGeneral,
    onConfirmar,
    onCerrar,
}: Propiedades): ReactElement {
    const [fecha, setFecha] = useState(fechaActual);
    const [hora, setHora] = useState(horaActual);
    const [forzar, setForzar] = useState(false);

    return (
        <Modal titulo="Reprogramar cita" onCerrar={onCerrar}>
            <div className="space-y-4">
                {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

                <div className="grid grid-cols-2 gap-4">
                    <CampoTexto etiqueta="Fecha" type="date" value={fecha} onChange={(evento) => setFecha(evento.target.value)} />
                    <CampoTexto etiqueta="Hora" type="time" value={hora} onChange={(evento) => setHora(evento.target.value)} />
                </div>

                {puedeForzar && (
                    <CampoInterruptor
                        etiqueta="Forzar"
                        ayuda="Ignora la disponibilidad del profesional."
                        value={forzar}
                        onChange={setForzar}
                    />
                )}

                <Boton onClick={() => onConfirmar({ fecha, hora, forzar })} disabled={enviando} className="w-full">
                    {enviando ? 'Guardando...' : 'Confirmar'}
                </Boton>
            </div>
        </Modal>
    );
}
