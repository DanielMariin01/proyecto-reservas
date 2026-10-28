import { useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import { obtenerDiasConDisponibilidad, obtenerHorasDisponibles } from '@/flujo-reserva/api/clientePublico';
import CalendarioMes from '@/flujo-reserva/CalendarioMes';
import Boton from '@/componentes/Boton';
import SelectorDeHora from '@/componentes/SelectorDeHora';
import Tarjeta from '@/componentes/Tarjeta';
import type { CitaPorToken, HoraDisponible } from '@/tipos';

interface PropiedadesFormularioReprogramar {
    slug: string;
    cita: CitaPorToken;
    enviando: boolean;
    error: string | null;
    onConfirmar: (inicio: string) => void;
    onVolver: () => void;
}

export default function FormularioReprogramar({
    slug,
    cita,
    enviando,
    error,
    onConfirmar,
    onVolver,
}: PropiedadesFormularioReprogramar): ReactElement {
    const hoy = useMemo(() => new Date(), []);
    const [anio, setAnio] = useState(hoy.getFullYear());
    const [mes, setMes] = useState(hoy.getMonth() + 1);
    const [diaSeleccionado, setDiaSeleccionado] = useState<string | null>(null);
    const [horaSeleccionada, setHoraSeleccionada] = useState<HoraDisponible | null>(null);

    const mesTexto = `${anio}-${String(mes).padStart(2, '0')}`;
    const diasQuery = useQuery({
        queryKey: ['reprogramar-dias', slug, cita.servicioId, cita.profesionalId, mesTexto],
        queryFn: () => obtenerDiasConDisponibilidad(slug, cita.servicioId, cita.profesionalId, mesTexto),
    });

    const horasQuery = useQuery({
        queryKey: ['reprogramar-horas', slug, cita.servicioId, cita.profesionalId, diaSeleccionado],
        queryFn: () => obtenerHorasDisponibles(slug, cita.servicioId, cita.profesionalId, diaSeleccionado as string),
        enabled: diaSeleccionado !== null,
    });

    function cambiarMes(delta: number): void {
        const fecha = new Date(anio, mes - 1 + delta, 1);
        setAnio(fecha.getFullYear());
        setMes(fecha.getMonth() + 1);
        setDiaSeleccionado(null);
        setHoraSeleccionada(null);
    }

    return (
        <Tarjeta className="w-full max-w-md">
            <h2 className="text-xl font-semibold text-tinta">Elige una nueva fecha y hora</h2>

            <div className="mt-4">
                <CalendarioMes
                    anio={anio}
                    mes={mes}
                    diasConDisponibilidad={diasQuery.data ?? []}
                    diaSeleccionado={diaSeleccionado}
                    onSeleccionarDia={(dia) => {
                        setDiaSeleccionado(dia);
                        setHoraSeleccionada(null);
                    }}
                    onCambiarMes={cambiarMes}
                    cargando={diasQuery.isLoading}
                />
            </div>

            {diaSeleccionado && (
                <div className="mt-4 space-y-2">
                    {(horasQuery.data ?? []).length === 0 && !horasQuery.isLoading && (
                        <p className="text-sm text-texto-suave">No hay horas este día.</p>
                    )}
                    {(horasQuery.data ?? []).map((hora) => (
                        <SelectorDeHora
                            key={hora.inicio}
                            texto={hora.horaVisible}
                            seleccionado={horaSeleccionada?.inicio === hora.inicio}
                            onElegir={() => setHoraSeleccionada(hora)}
                        />
                    ))}
                </div>
            )}

            {error && <p className="mt-4 text-sm text-red-600">{error}</p>}

            <div className="mt-6 flex flex-col gap-2">
                <Boton
                    onClick={() => horaSeleccionada && onConfirmar(horaSeleccionada.inicio)}
                    disabled={enviando || !horaSeleccionada}
                    className="w-full"
                >
                    {enviando ? 'Guardando...' : 'Confirmar nueva hora'}
                </Boton>
                <Boton variante="contorno" onClick={onVolver} disabled={enviando} className="w-full">
                    Volver
                </Boton>
            </div>
        </Tarjeta>
    );
}
