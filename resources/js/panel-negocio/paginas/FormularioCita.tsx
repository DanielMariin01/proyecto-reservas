import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoInterruptor from '@/componentes/CampoInterruptor';
import CampoTelefono from '@/componentes/CampoTelefono';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import { type DatosCrearCita, type ErroresValidacion, ErrorDeValidacion } from '../api/citas';
import { obtenerProfesionales } from '../api/profesionales';
import { obtenerServicios } from '../api/servicios';

interface Propiedades {
    slug: string;
    puedeForzar: boolean;
    enviando: boolean;
    errorGeneral: string | null;
    onGuardar: (datos: DatosCrearCita) => Promise<unknown>;
}

export default function FormularioCita({ slug, puedeForzar, enviando, errorGeneral, onGuardar }: Propiedades): ReactElement {
    const [profesionalId, setProfesionalId] = useState('');
    const [servicioId, setServicioId] = useState('');
    const [fecha, setFecha] = useState('');
    const [hora, setHora] = useState('');
    const [nombreCliente, setNombreCliente] = useState('');
    const [celularCliente, setCelularCliente] = useState('');
    const [correoCliente, setCorreoCliente] = useState('');
    const [notas, setNotas] = useState('');
    const [forzar, setForzar] = useState(false);
    const [errores, setErrores] = useState<ErroresValidacion>({});

    const consultaProfesionales = useQuery({
        queryKey: ['panel-profesionales-para-crear-cita', slug],
        queryFn: () => obtenerProfesionales(slug),
    });

    const consultaServicios = useQuery({
        queryKey: ['panel-servicios-para-crear-cita', slug],
        queryFn: () => obtenerServicios(slug),
    });

    async function enviar(): Promise<void> {
        setErrores({});

        try {
            await onGuardar({
                profesional_id: profesionalId ? Number(profesionalId) : null,
                servicio_id: Number(servicioId),
                fecha,
                hora,
                nombre_cliente: nombreCliente,
                celular_cliente: celularCliente,
                correo_cliente: correoCliente,
                notas,
                forzar,
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
        <Tarjeta className="max-w-2xl space-y-6">
            {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

            <div className="space-y-4">
                <h3 className="text-sm font-semibold text-tinta">Cita</h3>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label htmlFor="cita-profesional" className="text-sm font-medium text-tinta">
                            Profesional
                        </label>
                        <select
                            id="cita-profesional"
                            value={profesionalId}
                            onChange={(evento) => setProfesionalId(evento.target.value)}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Cualquiera disponible</option>
                            {(consultaProfesionales.data ?? [])
                                .filter((profesional) => profesional.activo)
                                .map((profesional) => (
                                    <option key={profesional.id} value={profesional.id}>
                                        {profesional.nombre}
                                    </option>
                                ))}
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="cita-servicio" className="text-sm font-medium text-tinta">
                            Servicio
                        </label>
                        <select
                            id="cita-servicio"
                            value={servicioId}
                            onChange={(evento) => setServicioId(evento.target.value)}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Elige un servicio</option>
                            {(consultaServicios.data ?? [])
                                .filter((servicio) => servicio.visible)
                                .map((servicio) => (
                                    <option key={servicio.id} value={servicio.id}>
                                        {servicio.nombre}
                                    </option>
                                ))}
                        </select>
                        {errores.servicio_id && <p className="text-sm text-red-600">{errores.servicio_id[0]}</p>}
                    </div>

                    <CampoTexto etiqueta="Fecha" type="date" value={fecha} onChange={(evento) => setFecha(evento.target.value)} error={errores.fecha?.[0]} />
                    <CampoTexto etiqueta="Hora" type="time" value={hora} onChange={(evento) => setHora(evento.target.value)} error={errores.hora?.[0]} />
                </div>
            </div>

            <div className="space-y-4 border-t border-gris-claro pt-4">
                <div>
                    <h3 className="text-sm font-semibold text-tinta">Cliente</h3>
                    <p className="text-sm text-texto-suave">Si el celular ya existe en este negocio, se usan sus datos guardados.</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <CampoTexto
                        etiqueta="Nombre"
                        value={nombreCliente}
                        onChange={(evento) => setNombreCliente(evento.target.value)}
                        error={errores.nombre_cliente?.[0]}
                    />
                    <CampoTelefono
                        etiqueta="Celular"
                        value={celularCliente}
                        onChange={(evento) => setCelularCliente(evento.target.value)}
                        error={errores.celular_cliente?.[0]}
                    />
                    <CampoTexto
                        etiqueta="Correo (opcional)"
                        type="email"
                        value={correoCliente}
                        onChange={(evento) => setCorreoCliente(evento.target.value)}
                        error={errores.correo_cliente?.[0]}
                    />
                </div>
            </div>

            <div className="space-y-4 border-t border-gris-claro pt-4">
                <h3 className="text-sm font-semibold text-tinta">Notas y excepciones</h3>

                <div className="flex flex-col gap-1">
                    <label htmlFor="cita-notas" className="text-sm font-medium text-tinta">
                        Nota para el profesional
                    </label>
                    <textarea
                        id="cita-notas"
                        value={notas}
                        onChange={(evento) => setNotas(evento.target.value)}
                        rows={3}
                        className="rounded-boton border border-gris-claro px-3 py-2 text-tinta focus:border-primario focus:outline-none"
                    />
                </div>

                {puedeForzar && (
                    <CampoInterruptor
                        etiqueta="Forzar"
                        ayuda="Guarda la cita aunque la hora esté fuera del horario o ya ocupada."
                        value={forzar}
                        onChange={setForzar}
                    />
                )}
            </div>

            <Boton onClick={enviar} disabled={enviando}>
                {enviando ? 'Guardando...' : 'Crear cita'}
            </Boton>
        </Tarjeta>
    );
}
