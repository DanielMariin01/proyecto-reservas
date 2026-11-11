import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosUsuario, ErrorDeValidacion, type ErroresValidacion } from '../api/usuarios';

interface Propiedades {
    valoresIniciales?: DatosUsuario;
    enviando: boolean;
    onGuardar: (datos: DatosUsuario) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioUsuario({ valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [email, setEmail] = useState(valoresIniciales?.email ?? '');
    const [telefono, setTelefono] = useState(valoresIniciales?.telefono ?? '');
    const [esSuperAdmin, setEsSuperAdmin] = useState(valoresIniciales?.es_super_admin ?? false);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({ nombre, email, telefono, es_super_admin: esSuperAdmin });
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Tarjeta className="max-w-xl space-y-4">
            <CampoTexto etiqueta="Nombre" value={nombre} onChange={(evento) => setNombre(evento.target.value)} error={errores.nombre?.[0]} />

            <CampoTexto
                etiqueta="Correo"
                type="email"
                value={email}
                onChange={(evento) => setEmail(evento.target.value)}
                error={errores.email?.[0]}
            />

            <CampoTexto
                etiqueta="Teléfono"
                type="tel"
                value={telefono}
                onChange={(evento) => setTelefono(evento.target.value)}
                error={errores.telefono?.[0]}
            />

            <CampoInterruptor
                etiqueta="Es super admin"
                ayuda="Entra al panel admin y puede gestionar todos los negocios."
                value={esSuperAdmin}
                onChange={setEsSuperAdmin}
            />

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = '/admin/usuarios')}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
