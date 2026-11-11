import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import Boton from '@/componentes/Boton';
import {
    anularPagoAgenda,
    cambiarEstadoCitaAgenda,
    cancelarCitaAgenda,
    type CitaAgenda,
    completarConPagoAgenda,
    esErrorDeAccionCita,
    obtenerAgenda,
    reprogramarCitaAgenda,
} from '../api/agenda';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalCambiarEstadoCita from './ModalCambiarEstadoCita';
import ModalCompletarConPago from './ModalCompletarConPago';
import ModalMotivo from './ModalMotivo';
import ModalReprogramarCita from './ModalReprogramarCita';
import type { AccionCita } from './TarjetaCitaAgenda';
import TarjetaCitaAgenda from './TarjetaCitaAgenda';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

function hoyISO(): string {
    return new Date().toISOString().slice(0, 10);
}

function sumarDias(fechaISO: string, dias: number): string {
    const fecha = new Date(`${fechaISO}T00:00:00`);
    fecha.setDate(fecha.getDate() + dias);

    return fecha.toISOString().slice(0, 10);
}

export default function PaginaAgendaDelDia({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [fecha, setFecha] = useState(hoyISO());
    const [accion, setAccion] = useState<{ tipo: AccionCita; cita: CitaAgenda } | null>(null);
    const [errorGeneral, setErrorGeneral] = useState<string | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-agenda', negocioSlug, fecha],
        queryFn: () => obtenerAgenda(negocioSlug, fecha),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-agenda', negocioSlug, fecha] });
    }

    function cerrarAccion(): void {
        setAccion(null);
        setErrorGeneral(null);
    }

    const mutacionCambiarEstado = useMutation({
        mutationFn: (estado: string) => cambiarEstadoCitaAgenda(negocioSlug, accion!.cita.id, estado),
        onSuccess: () => {
            cerrarAccion();
            invalidar();
        },
        onError: (error) => manejarError(error),
    });

    const mutacionCompletarConPago = useMutation({
        mutationFn: (datos: { metodo: string; valor: number; propina: number }) =>
            completarConPagoAgenda(negocioSlug, accion!.cita.id, datos),
        onSuccess: () => {
            cerrarAccion();
            invalidar();
        },
        onError: (error) => manejarError(error),
    });

    const mutacionCancelar = useMutation({
        mutationFn: (motivo: string) => cancelarCitaAgenda(negocioSlug, accion!.cita.id, motivo),
        onSuccess: () => {
            cerrarAccion();
            invalidar();
        },
        onError: (error) => manejarError(error),
    });

    const mutacionReprogramar = useMutation({
        mutationFn: (datos: { fecha: string; hora: string; forzar: boolean }) =>
            reprogramarCitaAgenda(negocioSlug, accion!.cita.id, datos),
        onSuccess: () => {
            cerrarAccion();
            invalidar();
        },
        onError: (error) => manejarError(error),
    });

    const mutacionAnularPago = useMutation({
        mutationFn: (motivo: string) => anularPagoAgenda(negocioSlug, accion!.cita.id, motivo),
        onSuccess: () => {
            cerrarAccion();
            invalidar();
        },
        onError: (error) => manejarError(error),
    });

    function manejarError(error: unknown): void {
        setErrorGeneral(esErrorDeAccionCita(error) ? error.message : 'Algo falló. Código REF-AGN0.');
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            aria-label="Día anterior"
                            onClick={() => setFecha((actual) => sumarDias(actual, -1))}
                            className="flex h-11 w-11 items-center justify-center rounded-boton border border-gris-claro text-tinta"
                        >
                            <ChevronLeft size={18} strokeWidth={1.75} />
                        </button>
                        <span className="text-base font-semibold text-tinta capitalize">{consulta.data?.fechaEtiqueta ?? '...'}</span>
                        <button
                            type="button"
                            aria-label="Día siguiente"
                            onClick={() => setFecha((actual) => sumarDias(actual, 1))}
                            className="flex h-11 w-11 items-center justify-center rounded-boton border border-gris-claro text-tinta"
                        >
                            <ChevronRight size={18} strokeWidth={1.75} />
                        </button>
                    </div>

                    <Boton variante="contorno" onClick={() => setFecha(hoyISO())}>
                        Hoy
                    </Boton>
                </div>

                {consulta.isLoading ? (
                    <p className="text-sm text-texto-suave">Cargando...</p>
                ) : consulta.isError ? (
                    <p className="text-sm text-texto-suave">Algo falló cargando la agenda. Código REF-AGN1.</p>
                ) : (consulta.data?.columnas.length ?? 0) === 0 ? (
                    <p className="text-sm text-texto-suave">Aún no hay profesionales activos. Crea uno en Profesionales para que aparezca aquí.</p>
                ) : (
                    <div className="grid gap-4 overflow-x-auto" style={{ gridTemplateColumns: `repeat(${consulta.data!.columnas.length}, minmax(240px, 1fr))` }}>
                        {consulta.data!.columnas.map((columna) => (
                            <div key={columna.profesionalId}>
                                <div className="mb-2 flex items-center gap-2">
                                    <span
                                        className="inline-block h-3 w-3 rounded-full"
                                        style={{ backgroundColor: columna.profesionalColor ?? '#673DE6' }}
                                    />
                                    <span className="text-sm font-semibold text-tinta">{columna.profesionalNombre}</span>
                                </div>

                                {columna.citas.length === 0 ? (
                                    <p className="text-sm text-texto-suave">Sin citas este día.</p>
                                ) : (
                                    <ul className="space-y-2">
                                        {columna.citas.map((cita) => (
                                            <TarjetaCitaAgenda
                                                key={cita.id}
                                                cita={cita}
                                                colorProfesional={columna.profesionalColor}
                                                onAbrirAccion={(tipo) => {
                                                    setErrorGeneral(null);
                                                    setAccion({ tipo, cita });
                                                }}
                                            />
                                        ))}
                                    </ul>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {accion?.tipo === 'cambiarEstado' && (
                <ModalCambiarEstadoCita
                    destinos={accion.cita.acciones.destinosCambioEstado}
                    enviando={mutacionCambiarEstado.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(estado) => mutacionCambiarEstado.mutate(estado)}
                    onCerrar={cerrarAccion}
                />
            )}

            {accion?.tipo === 'completarConPago' && (
                <ModalCompletarConPago
                    precioSugerido={accion.cita.precio}
                    enviando={mutacionCompletarConPago.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(datos) => mutacionCompletarConPago.mutate(datos)}
                    onCerrar={cerrarAccion}
                />
            )}

            {accion?.tipo === 'cancelar' && (
                <ModalMotivo
                    titulo="Cancelar cita"
                    etiqueta="Motivo"
                    enviando={mutacionCancelar.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(motivo) => mutacionCancelar.mutate(motivo)}
                    onCerrar={cerrarAccion}
                />
            )}

            {accion?.tipo === 'reprogramar' && (
                <ModalReprogramarCita
                    fechaActual={accion.cita.fecha}
                    horaActual={accion.cita.horaHM}
                    puedeForzar={accion.cita.acciones.puedeForzarReprogramacion}
                    enviando={mutacionReprogramar.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(datos) => mutacionReprogramar.mutate(datos)}
                    onCerrar={cerrarAccion}
                />
            )}

            {accion?.tipo === 'anularPago' && (
                <ModalMotivo
                    titulo="Anular pago"
                    etiqueta="Motivo"
                    enviando={mutacionAnularPago.isPending}
                    errorGeneral={errorGeneral}
                    onConfirmar={(motivo) => mutacionAnularPago.mutate(motivo)}
                    onCerrar={cerrarAccion}
                />
            )}
        </LayoutPanelNegocio>
    );
}
