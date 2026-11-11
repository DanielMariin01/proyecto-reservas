import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Modal from '@/componentes/Modal';
import { type Bloqueo, type DatosBloqueo, ErrorDeValidacion, type ErroresValidacion } from '../api/bloqueos';

interface Propiedades {
    bloqueo?: Bloqueo;
    enviando: boolean;
    onGuardar: (datos: DatosBloqueo) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalBloqueo({ bloqueo, enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const hoy = new Date().toISOString().slice(0, 10);
    const [tipo, setTipo] = useState<string>(bloqueo?.tipo ?? 'bloqueo');
    const [fechaInicio, setFechaInicio] = useState(bloqueo?.fechaInicio ?? hoy);
    const [horaInicio, setHoraInicio] = useState(bloqueo?.horaInicio ?? '09:00');
    const [fechaFin, setFechaFin] = useState(bloqueo?.fechaFin ?? hoy);
    const [horaFin, setHoraFin] = useState(bloqueo?.horaFin ?? '10:00');
    const [motivo, setMotivo] = useState(bloqueo?.motivo ?? '');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({
                tipo,
                fecha_inicio: fechaInicio,
                hora_inicio: horaInicio,
                fecha_fin: fechaFin,
                hora_fin: horaFin,
                motivo,
            });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Modal titulo={bloqueo ? 'Editar bloqueo' : 'Nuevo bloqueo'} onCerrar={onCerrar}>
            <div className="space-y-4">
                <div className="flex flex-col gap-1">
                    <label htmlFor="bloqueo-tipo" className="text-sm font-medium text-tinta">
                        Tipo
                    </label>
                    <select
                        id="bloqueo-tipo"
                        value={tipo}
                        onChange={(evento) => setTipo(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        <option value="bloqueo">Bloqueo (no atiendo)</option>
                        <option value="apertura">Apertura especial</option>
                    </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <CampoTexto
                        etiqueta="Desde (fecha)"
                        type="date"
                        value={fechaInicio}
                        onChange={(evento) => setFechaInicio(evento.target.value)}
                        error={errores.fecha_inicio?.[0]}
                    />
                    <CampoTexto
                        etiqueta="Desde (hora)"
                        type="time"
                        value={horaInicio}
                        onChange={(evento) => setHoraInicio(evento.target.value)}
                        error={errores.hora_inicio?.[0]}
                    />
                    <CampoTexto
                        etiqueta="Hasta (fecha)"
                        type="date"
                        value={fechaFin}
                        onChange={(evento) => setFechaFin(evento.target.value)}
                        error={errores.fecha_fin?.[0]}
                    />
                    <CampoTexto
                        etiqueta="Hasta (hora)"
                        type="time"
                        value={horaFin}
                        onChange={(evento) => setHoraFin(evento.target.value)}
                        error={errores.hora_fin?.[0]}
                    />
                </div>

                <CampoTexto etiqueta="Motivo" value={motivo} onChange={(evento) => setMotivo(evento.target.value)} error={errores.motivo?.[0]} />

                <Boton onClick={enviar} disabled={enviando} className="w-full">
                    {enviando ? 'Guardando...' : 'Guardar'}
                </Boton>
            </div>
        </Modal>
    );
}
