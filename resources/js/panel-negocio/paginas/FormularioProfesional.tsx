import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import CampoColor from '@/componentes/CampoColor';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import {
    type DatosCrearProfesional,
    type DatosProfesional,
    ErrorDeValidacion,
    type ErroresValidacion,
    type Profesional,
} from '../api/profesionales';
import CampoFotoProfesional from './CampoFotoProfesional';

interface Propiedades {
    slug: string;
    esCreacion: boolean;
    valoresIniciales?: Profesional;
    enviando: boolean;
    onGuardar: (datos: DatosProfesional | DatosCrearProfesional) => Promise<unknown>;
    textoBoton: string;
}

export default function FormularioProfesional({ slug, esCreacion, valoresIniciales, enviando, onGuardar, textoBoton }: Propiedades): ReactElement {
    const [nombre, setNombre] = useState(valoresIniciales?.nombre ?? '');
    const [especialidad, setEspecialidad] = useState(valoresIniciales?.especialidad ?? '');
    const [foto, setFoto] = useState<string | null>(valoresIniciales?.foto ?? null);
    const [biografia, setBiografia] = useState(valoresIniciales?.biografia ?? '');
    const [certificaciones, setCertificaciones] = useState(valoresIniciales?.certificaciones ?? '');
    const [color, setColor] = useState(valoresIniciales?.color ?? '#673DE6');
    const [activo, setActivo] = useState(valoresIniciales?.activo ?? true);
    const [nombreUsuario, setNombreUsuario] = useState('');
    const [emailUsuario, setEmailUsuario] = useState('');
    const [errores, setErrores] = useState<ErroresValidacion>({});

    async function enviar(): Promise<void> {
        setErrores({});

        const datos: DatosProfesional = { nombre, especialidad, foto, biografia, certificaciones, color, activo };

        try {
            if (esCreacion) {
                await onGuardar({ ...datos, nombre_usuario: nombreUsuario, email_usuario: emailUsuario });

                return;
            }

            await onGuardar(datos);
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <Tarjeta className="max-w-2xl space-y-6">
            <div className="grid gap-4 sm:grid-cols-2">
                <CampoTexto etiqueta="Nombre" value={nombre} onChange={(evento) => setNombre(evento.target.value)} error={errores.nombre?.[0]} />
                <CampoTexto
                    etiqueta="Especialidad"
                    value={especialidad}
                    onChange={(evento) => setEspecialidad(evento.target.value)}
                    error={errores.especialidad?.[0]}
                />
            </div>

            <CampoFotoProfesional slug={slug} value={foto} onChange={setFoto} />

            <div className="flex flex-col gap-1">
                <label htmlFor="profesional-biografia" className="text-sm font-medium text-tinta">
                    Biografía
                </label>
                <textarea
                    id="profesional-biografia"
                    value={biografia}
                    onChange={(evento) => setBiografia(evento.target.value)}
                    rows={3}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
            </div>

            <div className="flex flex-col gap-1">
                <label htmlFor="profesional-certificaciones" className="text-sm font-medium text-tinta">
                    Certificaciones
                </label>
                <textarea
                    id="profesional-certificaciones"
                    value={certificaciones}
                    onChange={(evento) => setCertificaciones(evento.target.value)}
                    rows={3}
                    className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                />
            </div>

            <CampoColor etiqueta="Color" value={color} onChange={setColor} />

            {!esCreacion && <CampoInterruptor etiqueta="Activo" value={activo} onChange={setActivo} />}

            {esCreacion && (
                <div className="space-y-4 border-t border-gris-claro pt-4">
                    <div>
                        <h4 className="text-sm font-semibold text-tinta">Usuario para entrar al panel</h4>
                        <p className="text-sm text-texto-suave">
                            Opcional: crea un usuario con rol de profesional y le envía un correo para que fije su contraseña.
                        </p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <CampoTexto
                            etiqueta="Nombre"
                            value={nombreUsuario}
                            onChange={(evento) => setNombreUsuario(evento.target.value)}
                            error={errores.nombre_usuario?.[0]}
                        />
                        <CampoTexto
                            etiqueta="Correo"
                            type="email"
                            value={emailUsuario}
                            onChange={(evento) => setEmailUsuario(evento.target.value)}
                            error={errores.email_usuario?.[0]}
                        />
                    </div>
                </div>
            )}

            <div className="flex gap-2">
                <Boton onClick={enviar} disabled={enviando}>
                    {enviando ? 'Guardando...' : textoBoton}
                </Boton>
                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${slug}/profesionales`)}>
                    Cancelar
                </Boton>
            </div>
        </Tarjeta>
    );
}
