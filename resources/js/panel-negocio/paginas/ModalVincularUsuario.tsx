import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Modal from '@/componentes/Modal';
import { type DatosVincularUsuario, ErrorDeValidacion, type ErroresValidacion } from '../api/profesionales';

interface Propiedades {
    enviando: boolean;
    onGuardar: (datos: DatosVincularUsuario) => Promise<unknown>;
    onCerrar: () => void;
}

export default function ModalVincularUsuario({ enviando, onGuardar, onCerrar }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState('');
    const [email, setEmail] = useState('');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({ nombre, email });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Modal titulo="Vincular usuario" onCerrar={onCerrar}>
            <div className="space-y-4">
                <CampoTexto etiqueta="Nombre" value={nombre} onChange={(evento) => setNombre(evento.target.value)} error={errores.nombre?.[0]} />
                <CampoTexto
                    etiqueta="Correo"
                    type="email"
                    value={email}
                    onChange={(evento) => setEmail(evento.target.value)}
                    error={errores.email?.[0]}
                />
                <p className="text-sm text-texto-suave">Se le enviará un correo para que elija su contraseña.</p>

                <Boton onClick={enviar} disabled={enviando} className="w-full">
                    {enviando ? 'Invitando...' : 'Invitar'}
                </Boton>
            </div>
        </Modal>
    );
}
