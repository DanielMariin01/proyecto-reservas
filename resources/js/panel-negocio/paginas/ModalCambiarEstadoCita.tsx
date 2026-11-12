import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import Modal from '@/componentes/Modal';
import type { DestinoEstado } from '../api/agenda';

interface Propiedades {
    destinos: DestinoEstado[];
    enviando: boolean;
    errorGeneral: string | null;
    onConfirmar: (estado: string) => void;
    onCerrar: () => void;
}

export default function ModalCambiarEstadoCita({ destinos, enviando, errorGeneral, onConfirmar, onCerrar }: Propiedades): ReactElement {
    const [estado, setEstado] = useState(destinos[0]?.value ?? '');

    return (
        <Modal titulo="Cambiar estado" onCerrar={onCerrar}>
            <div className="space-y-4">
                {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

                <div className="flex flex-col gap-1">
                    <label htmlFor="cambiar-estado-cita" className="text-sm font-medium text-tinta">
                        Nuevo estado
                    </label>
                    <select
                        id="cambiar-estado-cita"
                        value={estado}
                        onChange={(evento) => setEstado(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        {destinos.map((destino) => (
                            <option key={destino.value} value={destino.value}>
                                {destino.label}
                            </option>
                        ))}
                    </select>
                </div>

                <Boton onClick={() => onConfirmar(estado)} disabled={enviando || estado === ''} className="w-full">
                    {enviando ? 'Guardando...' : 'Confirmar'}
                </Boton>
            </div>
        </Modal>
    );
}
