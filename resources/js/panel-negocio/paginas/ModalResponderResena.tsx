import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import Modal from '@/componentes/Modal';
import type { ResenaNegocio } from '../api/resenas';

interface Propiedades {
    resena: ResenaNegocio;
    enviando: boolean;
    onGuardar: (respuesta: string) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalResponderResena({ resena, enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const [respuesta, setRespuesta] = useState(resena.respuestaTexto ?? '');
    const [error, setError] = useState<string | null>(null);

    async function enviar(): Promise<void> {
        setError(null);

        try {
            await onGuardar(respuesta);
        } catch {
            setError('No se pudo guardar la respuesta.');
        }
    }

    return (
        <Modal titulo={resena.respuestaTexto === null ? 'Responder' : 'Editar respuesta'} onCerrar={onCerrar}>
            <div className="space-y-4">
                <div className="flex flex-col gap-1">
                    <label htmlFor="respuesta-resena" className="text-sm font-medium text-tinta">
                        Tu respuesta
                    </label>
                    <textarea
                        id="respuesta-resena"
                        value={respuesta}
                        onChange={(evento) => setRespuesta(evento.target.value)}
                        rows={4}
                        className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                    />
                    {error && <p className="text-sm text-red-600">{error}</p>}
                </div>

                <Boton onClick={enviar} disabled={enviando} className="w-full">
                    {enviando ? 'Guardando...' : 'Publicar respuesta'}
                </Boton>
            </div>
        </Modal>
    );
}
