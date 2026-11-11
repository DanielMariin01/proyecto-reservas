import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import Modal from '@/componentes/Modal';

interface Propiedades {
    titulo: string;
    etiqueta: string;
    enviando: boolean;
    errorGeneral: string | null;
    onConfirmar: (motivo: string) => void;
    onCerrar: () => void;
}

export default function ModalMotivo({ titulo, etiqueta, enviando, errorGeneral, onConfirmar, onCerrar }: Propiedades): ReactElement {
    const [motivo, setMotivo] = useState('');

    return (
        <Modal titulo={titulo} onCerrar={onCerrar}>
            <div className="space-y-4">
                {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

                <div className="flex flex-col gap-1">
                    <label htmlFor="motivo-cita" className="text-sm font-medium text-tinta">
                        {etiqueta}
                    </label>
                    <textarea
                        id="motivo-cita"
                        value={motivo}
                        onChange={(evento) => setMotivo(evento.target.value)}
                        rows={3}
                        className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                    />
                </div>

                <Boton onClick={() => onConfirmar(motivo)} disabled={enviando || motivo.trim() === ''} className="w-full">
                    {enviando ? 'Guardando...' : 'Confirmar'}
                </Boton>
            </div>
        </Modal>
    );
}
