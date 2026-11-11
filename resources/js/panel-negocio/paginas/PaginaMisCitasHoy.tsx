import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Insignia from '@/componentes/Insignia';
import Tarjeta from '@/componentes/Tarjeta';
import {
    type CitaMisCitasHoy,
    completarConPagoPropio,
    esErrorDeAccionCita,
    marcarEnAtencion,
    marcarNoAsistio,
    obtenerMisCitasHoy,
} from '../api/misCitasHoy';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalCompletarConPago from './ModalCompletarConPago';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaMisCitasHoy({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [citaPendientePago, setCitaPendientePago] = useState<CitaMisCitasHoy | null>(null);
    const [errorGeneral, setErrorGeneral] = useState<string | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-mis-citas-hoy', negocioSlug],
        queryFn: () => obtenerMisCitasHoy(negocioSlug),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-mis-citas-hoy', negocioSlug] });
    }

    const mutacionEnAtencion = useMutation({
        mutationFn: (citaId: number) => marcarEnAtencion(negocioSlug, citaId),
        onSuccess: invalidar,
    });

    const mutacionNoAsistio = useMutation({
        mutationFn: (citaId: number) => marcarNoAsistio(negocioSlug, citaId),
        onSuccess: invalidar,
    });

    const mutacionCompletar = useMutation({
        mutationFn: (datos: { metodo: string; valor: number; propina: number }) =>
            completarConPagoPropio(negocioSlug, citaPendientePago!.id, datos),
        onSuccess: () => {
            setCitaPendientePago(null);
            setErrorGeneral(null);
            invalidar();
        },
        onError: (error) => setErrorGeneral(esErrorDeAccionCita(error) ? error.message : 'Algo falló. Código REF-MCH0.'),
    });

    const citas = consulta.data?.citas ?? [];

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Mis citas de hoy</h2>

                {consulta.isLoading ? (
                    <p className="text-sm text-texto-suave">Cargando...</p>
                ) : consulta.isError ? (
                    <p className="text-sm text-texto-suave">Algo falló cargando tus citas. Código REF-MCH1.</p>
                ) : citas.length === 0 ? (
                    <p className="text-sm text-texto-suave">Aún no tienes citas hoy. Cuando te reserven una hora hoy, aparecerá aquí.</p>
                ) : (
                    <div className="space-y-4">
                        {citas.map((cita) => (
                            <Tarjeta key={cita.id}>
                                <div className="flex items-center justify-between gap-2">
                                    <span className="text-lg font-semibold text-tinta">{cita.hora}</span>
                                    {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={cita.estadoEtiqueta} />}
                                </div>

                                <p className="mt-2 text-base font-medium text-tinta">{cita.clienteNombre ?? '—'}</p>
                                <p className="text-sm text-texto-suave">{cita.servicioNombre ?? '—'}</p>
                                {cita.notas && <p className="mt-2 text-sm italic text-texto-suave">&ldquo;{cita.notas}&rdquo;</p>}

                                <div className="mt-4 flex flex-wrap gap-2">
                                    {cita.acciones.puedeMarcarEnAtencion && (
                                        <Boton
                                            variante="contorno"
                                            disabled={mutacionEnAtencion.isPending}
                                            onClick={() => mutacionEnAtencion.mutate(cita.id)}
                                        >
                                            En atención
                                        </Boton>
                                    )}
                                    {cita.acciones.puedeCompletar && (
                                        <Boton
                                            onClick={() => {
                                                setErrorGeneral(null);
                                                setCitaPendientePago(cita);
                                            }}
                                        >
                                            Completada
                                        </Boton>
                                    )}
                                    {cita.acciones.puedeMarcarNoAsistio && (
                                        <Boton
                                            variante="contorno"
                                            disabled={mutacionNoAsistio.isPending}
                                            onClick={() => mutacionNoAsistio.mutate(cita.id)}
                                        >
                                            No asistió
                                        </Boton>
                                    )}
                                </div>
                            </Tarjeta>
                        ))}
                    </div>
                )}
            </div>

            {citaPendientePago && (
                <ModalCompletarConPago
                    precioSugerido={citaPendientePago.precio}
                    enviando={mutacionCompletar.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(datos) => mutacionCompletar.mutate(datos)}
                    onCerrar={() => setCitaPendientePago(null)}
                />
            )}
        </LayoutPanelNegocio>
    );
}
