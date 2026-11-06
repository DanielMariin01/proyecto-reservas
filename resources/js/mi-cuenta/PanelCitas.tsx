import { LogOut } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import FormularioCancelar from '@/gestionar-cita/FormularioCancelar';
import FormularioReprogramar from '@/gestionar-cita/FormularioReprogramar';
import FormularioResena from '@/gestionar-cita/FormularioResena';
import type { CitaDeCuenta, CuentaCliente } from '@/tipos';
import {
    cancelarCitaDeCuenta,
    cerrarSesion,
    dejarResenaDeCuenta,
    esErrorDeGestionCita,
    obtenerMisCitas,
    reprogramarCitaDeCuenta,
} from './api/clienteCuenta';
import TarjetaCitaCliente from './TarjetaCitaCliente';

interface PropiedadesPanelCitas {
    cuenta: CuentaCliente;
    onSalir: () => void;
}

type Pestana = 'proximas' | 'historial';
type Accion = 'cancelar' | 'reprogramar' | 'resena';

const CLAVE_CITAS = ['mi-cuenta-citas'];

export default function PanelCitas({ cuenta, onSalir }: PropiedadesPanelCitas): ReactElement {
    const [pestana, setPestana] = useState<Pestana>('proximas');
    const [citaSeleccionada, setCitaSeleccionada] = useState<CitaDeCuenta | null>(null);
    const [accion, setAccion] = useState<Accion | null>(null);
    const queryClient = useQueryClient();

    const citasQuery = useQuery({ queryKey: CLAVE_CITAS, queryFn: obtenerMisCitas });

    const proximas = useMemo(() => (citasQuery.data ?? []).filter((cita) => cita.esProxima), [citasQuery.data]);
    const historial = useMemo(() => (citasQuery.data ?? []).filter((cita) => !cita.esProxima), [citasQuery.data]);

    function cerrarAccion(): void {
        setCitaSeleccionada(null);
        setAccion(null);
    }

    function alExito(): void {
        queryClient.invalidateQueries({ queryKey: CLAVE_CITAS });
        cerrarAccion();
    }

    const mutacionCancelar = useMutation({
        mutationFn: (motivo: string) => cancelarCitaDeCuenta(citaSeleccionada!.id, motivo),
        onSuccess: alExito,
    });

    const mutacionReprogramar = useMutation({
        mutationFn: (inicio: string) => reprogramarCitaDeCuenta(citaSeleccionada!.id, inicio),
        onSuccess: alExito,
    });

    const mutacionResena = useMutation({
        mutationFn: ({ calificacion, comentario }: { calificacion: number; comentario: string }) =>
            dejarResenaDeCuenta(citaSeleccionada!.id, calificacion, comentario),
        onSuccess: alExito,
    });

    async function salir(): Promise<void> {
        await cerrarSesion();
        onSalir();
    }

    if (citaSeleccionada && accion === 'cancelar') {
        return (
            <main className="flex min-h-screen items-center justify-center p-4">
                <FormularioCancelar
                    enviando={mutacionCancelar.isPending}
                    error={mensajeDeError(mutacionCancelar.error)}
                    onConfirmar={(motivo) => mutacionCancelar.mutate(motivo)}
                    onVolver={cerrarAccion}
                />
            </main>
        );
    }

    if (citaSeleccionada && accion === 'reprogramar') {
        return (
            <main className="flex min-h-screen items-center justify-center p-4">
                <FormularioReprogramar
                    slug={citaSeleccionada.negocioSlug}
                    cita={citaSeleccionada}
                    enviando={mutacionReprogramar.isPending}
                    error={mensajeDeError(mutacionReprogramar.error)}
                    onConfirmar={(inicio) => mutacionReprogramar.mutate(inicio)}
                    onVolver={cerrarAccion}
                />
            </main>
        );
    }

    if (citaSeleccionada && accion === 'resena') {
        return (
            <main className="flex min-h-screen items-center justify-center p-4">
                <FormularioResena
                    enviando={mutacionResena.isPending}
                    error={mensajeDeError(mutacionResena.error)}
                    onConfirmar={(calificacion, comentario) => mutacionResena.mutate({ calificacion, comentario })}
                    onVolver={cerrarAccion}
                />
            </main>
        );
    }

    const listaActual = pestana === 'proximas' ? proximas : historial;

    return (
        <main className="min-h-screen p-4 sm:p-8">
            <div className="mx-auto max-w-2xl">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                        <h1 className="truncate text-xl font-semibold text-tinta">Hola, {cuenta.nombre}</h1>
                        <p className="truncate text-sm text-texto-suave">{cuenta.correo}</p>
                    </div>
                    <button
                        onClick={salir}
                        type="button"
                        className="flex h-11 shrink-0 items-center gap-1 text-sm text-texto-suave hover:text-tinta"
                    >
                        <LogOut size={16} strokeWidth={1.75} /> Salir
                    </button>
                </div>

                <div className="mt-6 flex gap-2">
                    <button
                        type="button"
                        onClick={() => setPestana('proximas')}
                        className={`h-11 flex-1 rounded-boton text-sm font-semibold ${pestana === 'proximas' ? 'bg-primario text-blanco' : 'bg-blanco text-tinta'}`}
                    >
                        Próximas
                    </button>
                    <button
                        type="button"
                        onClick={() => setPestana('historial')}
                        className={`h-11 flex-1 rounded-boton text-sm font-semibold ${pestana === 'historial' ? 'bg-primario text-blanco' : 'bg-blanco text-tinta'}`}
                    >
                        Historial
                    </button>
                </div>

                <div className="mt-4 space-y-4">
                    {citasQuery.isLoading && (
                        <>
                            <div className="h-28 animate-pulse rounded-tarjeta bg-gris-claro" />
                            <div className="h-28 animate-pulse rounded-tarjeta bg-gris-claro" />
                        </>
                    )}

                    {citasQuery.isError && (
                        <div className="rounded-tarjeta bg-blanco p-6 text-center shadow-tarjeta">
                            <p className="text-sm text-texto-suave">Algo falló cargando tus citas. Código REF-CTA2.</p>
                            <Boton variante="contorno" className="mt-4" onClick={() => citasQuery.refetch()}>
                                Reintentar
                            </Boton>
                        </div>
                    )}

                    {citasQuery.data && pestana === 'proximas' && proximas.length === 0 && (
                        <p className="text-sm text-texto-suave">Aún no tienes citas próximas.</p>
                    )}

                    {citasQuery.data && pestana === 'historial' && historial.length === 0 && (
                        <p className="text-sm text-texto-suave">Todavía no tienes historial con nosotros.</p>
                    )}

                    {listaActual.map((cita) => (
                        <TarjetaCitaCliente
                            key={cita.id}
                            cita={cita}
                            onCancelar={() => {
                                setCitaSeleccionada(cita);
                                setAccion('cancelar');
                            }}
                            onReprogramar={() => {
                                setCitaSeleccionada(cita);
                                setAccion('reprogramar');
                            }}
                            onDejarResena={() => {
                                setCitaSeleccionada(cita);
                                setAccion('resena');
                            }}
                        />
                    ))}
                </div>
            </div>
        </main>
    );
}

function mensajeDeError(error: unknown): string | null {
    if (!error) {
        return null;
    }

    return esErrorDeGestionCita(error) ? error.message : 'Algo falló. Inténtalo de nuevo.';
}
