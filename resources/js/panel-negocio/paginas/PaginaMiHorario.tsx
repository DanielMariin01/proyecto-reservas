import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import Tarjeta from '@/componentes/Tarjeta';
import {
    crearBloqueoRapido,
    eliminarBloqueoRapido,
    ErrorDeValidacion,
    type ErroresValidacion,
    obtenerMiHorario,
    obtenerMiVistaPreviaDisponibilidad,
} from '../api/miHorario';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalVistaPreviaDisponibilidad from './ModalVistaPreviaDisponibilidad';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaMiHorario({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const hoy = new Date().toISOString().slice(0, 10);
    const [fecha, setFecha] = useState(hoy);
    const [horaInicio, setHoraInicio] = useState('');
    const [horaFin, setHoraFin] = useState('');
    const [motivo, setMotivo] = useState('');
    const [errores, setErrores] = useState<ErroresValidacion>({});
    const [modalVistaPreviaAbierto, setModalVistaPreviaAbierto] = useState(false);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-mi-horario', negocioSlug],
        queryFn: () => obtenerMiHorario(negocioSlug),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-mi-horario', negocioSlug] });
    }

    const mutacionCrearBloqueo = useMutation({
        mutationFn: () => crearBloqueoRapido(negocioSlug, { fecha, hora_inicio: horaInicio, hora_fin: horaFin, motivo }),
        onSuccess: () => {
            setHoraInicio('');
            setHoraFin('');
            setMotivo('');
            invalidar();
        },
    });

    const mutacionEliminarBloqueo = useMutation({
        mutationFn: (id: number) => eliminarBloqueoRapido(negocioSlug, id),
        onSuccess: invalidar,
    });

    async function crearBloqueo(): Promise<void> {
        setErrores({});

        try {
            await mutacionCrearBloqueo.mutateAsync();
        } catch (error) {
            if (error instanceof ErrorDeValidacion) {
                setErrores(error.errores);

                return;
            }

            throw error;
        }
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Mi horario</h2>
                    <Boton variante="contorno" onClick={() => setModalVistaPreviaAbierto(true)}>
                        Vista previa de disponibilidad
                    </Boton>
                </div>

                <Tarjeta className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold text-tinta">Bloqueo rápido</h3>
                        <p className="text-sm text-texto-suave">&ldquo;No atiendo hoy de 3 a 5&rdquo;: elige la fecha y el rango de horas.</p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <CampoTexto etiqueta="Fecha" type="date" value={fecha} onChange={(evento) => setFecha(evento.target.value)} error={errores.fecha?.[0]} />
                        <CampoTexto
                            etiqueta="Motivo (opcional)"
                            value={motivo}
                            onChange={(evento) => setMotivo(evento.target.value)}
                            error={errores.motivo?.[0]}
                        />
                        <CampoTexto
                            etiqueta="Desde"
                            type="time"
                            value={horaInicio}
                            onChange={(evento) => setHoraInicio(evento.target.value)}
                            error={errores.hora_inicio?.[0]}
                        />
                        <CampoTexto
                            etiqueta="Hasta"
                            type="time"
                            value={horaFin}
                            onChange={(evento) => setHoraFin(evento.target.value)}
                            error={errores.hora_fin?.[0]}
                        />
                    </div>

                    <Boton onClick={crearBloqueo} disabled={mutacionCrearBloqueo.isPending}>
                        {mutacionCrearBloqueo.isPending ? 'Bloqueando...' : 'Bloquear estas horas'}
                    </Boton>
                </Tarjeta>

                <Tarjeta>
                    <h3 className="text-base font-semibold text-tinta">Mi horario semanal</h3>

                    {consulta.isLoading ? (
                        <p className="mt-4 text-sm text-texto-suave">Cargando...</p>
                    ) : (consulta.data?.horarios ?? []).length === 0 ? (
                        <p className="mt-4 text-sm text-texto-suave">
                            Aún no tienes un horario. Pídele al dueño del negocio que lo configure.
                        </p>
                    ) : (
                        <ul className="mt-4 divide-y divide-gris-claro">
                            {(consulta.data?.horarios ?? []).map((horario) => (
                                <li key={horario.id} className="flex items-center justify-between py-2 text-sm">
                                    <span className="font-medium text-tinta">{horario.diaSemanaEtiqueta}</span>
                                    <span className="text-texto-suave">
                                        {horario.horaInicio} - {horario.horaFin}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </Tarjeta>

                <Tarjeta>
                    <h3 className="text-base font-semibold text-tinta">Mis próximos bloqueos</h3>

                    {consulta.isLoading ? (
                        <p className="mt-4 text-sm text-texto-suave">Cargando...</p>
                    ) : (consulta.data?.bloqueos ?? []).length === 0 ? (
                        <p className="mt-4 text-sm text-texto-suave">No tienes bloqueos próximos.</p>
                    ) : (
                        <ul className="mt-4 divide-y divide-gris-claro">
                            {(consulta.data?.bloqueos ?? []).map((bloqueo) => (
                                <li key={bloqueo.id} className="flex items-center justify-between py-2 text-sm">
                                    <span>
                                        <span className="font-medium text-tinta">{bloqueo.tipoEtiqueta}</span>{' '}
                                        <span className="text-texto-suave">{bloqueo.motivo ?? 'Sin motivo'}</span>
                                    </span>
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminarBloqueo.isPending}
                                        onClick={() => {
                                            if (window.confirm('¿Eliminar este bloqueo?')) {
                                                mutacionEliminarBloqueo.mutate(bloqueo.id);
                                            }
                                        }}
                                    >
                                        Eliminar
                                    </Boton>
                                </li>
                            ))}
                        </ul>
                    )}
                </Tarjeta>
            </div>

            {modalVistaPreviaAbierto && (
                <ModalVistaPreviaDisponibilidad
                    slug={negocioSlug}
                    onConsultar={(servicioId, fechaConsulta) => obtenerMiVistaPreviaDisponibilidad(negocioSlug, servicioId, fechaConsulta)}
                    onCerrar={() => setModalVistaPreviaAbierto(false)}
                />
            )}
        </LayoutPanelNegocio>
    );
}
