import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosActualizarUsuario, ErrorDeValidacion, type ErroresValidacion } from '../api/usuarios';
import { OPCIONES_ROL } from './opcionesRol';

interface Propiedades {
    slug: string;
    valoresIniciales: DatosActualizarUsuario;
    enviando: boolean;
    onGuardar: (datos: DatosActualizarUsuario) => Promise<unknown>;
}

export default function FormularioEditarUsuarioNegocio({ slug, valoresIniciales, enviando, onGuardar }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales.nombre);
    const [telefono, setTelefono] = useState(valoresIniciales.telefono);
    const [rol, setRol] = useState(valoresIniciales.rol);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({ nombre, telefono, rol });
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
                etiqueta="Teléfono"
                type="tel"
                value={telefono}
                onChange={(evento) => setTelefono(evento.target.value)}
                error={errores.telefono?.[0]}
            />

            <div className="flex flex-col gap-1">
                <label htmlFor="usuario-rol" className="text-sm font-medium text-tinta">
                    Rol
                </label>
                <select
                    id="usuario-rol"
                    value={rol}
                    onChange={(evento) => setRol(evento.target.value)}
                    className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                >
                    {OPCIONES_ROL.map((opcion) => (
                        <option key={opcion.valor} value={opcion.valor}>
                            {opcion.etiqueta}
                        </option>
                    ))}
                </select>
            </div>

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : 'Guardar cambios'}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${slug}/usuarios`)}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
