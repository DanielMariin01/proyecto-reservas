import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import Modal from '@/componentes/Modal';
import { type ErroresValidacion, ErrorDeValidacion } from '../api/negocios';

const OPCIONES_ESTADO: Array<{ valor: string; etiqueta: string }> = [
    { valor: 'activo', etiqueta: 'Reactivar' },
    { valor: 'suspendido', etiqueta: 'Suspender' },
    { valor: 'cancelado', etiqueta: 'Cancelar' },
];

interface Propiedades {
    enviando: boolean;
    onGuardar: (datos: { estado_destino: string; motivo: string }) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalCambiarEstado({ enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const [estadoDestino, setEstadoDestino] = useState('activo');
    const [motivo, setMotivo] = useState('');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({ estado_destino: estadoDestino, motivo });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Modal titulo="Suspender / cancelar / reactivar" onCerrar={onCerrar}>
            <div className="space-y-4">
                <div className="flex flex-col gap-1">
                    <label htmlFor="estado-destino" className="text-sm font-medium text-tinta">
                        Nuevo estado
                    </label>
                    <select
                        id="estado-destino"
                        value={estadoDestino}
                        onChange={(evento) => setEstadoDestino(evento.target.value)}
                        className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                    >
                        {OPCIONES_ESTADO.map((opcion) => (
                            <option key={opcion.valor} value={opcion.valor}>
                                {opcion.etiqueta}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="flex flex-col gap-1">
                    <label htmlFor="motivo-cambio" className="text-sm font-medium text-tinta">
                        Motivo
                    </label>
                    <textarea
                        id="motivo-cambio"
                        value={motivo}
                        onChange={(evento) => setMotivo(evento.target.value)}
                        rows={3}
                        className={`rounded-boton border px-3 py-2 text-tinta focus:border-primario focus:outline-none ${
                            errores.motivo ? 'border-red-500' : 'border-gris-claro'
                        }`}
                    />
                    {errores.motivo?.[0] && <p className="text-sm text-red-600">{errores.motivo[0]}</p>}
                </div>

                <Boton onClick={enviar} disabled={enviando} className="w-full">
                    {enviando ? 'Guardando...' : 'Confirmar'}
                </Boton>
            </div>
        </Modal>
    );
}
