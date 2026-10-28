import { useEffect, useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { crearReserva, esErrorDeReservaPublica, obtenerDiasConDisponibilidad, obtenerHorasDisponibles, obtenerProfesionales } from './api/clientePublico';
import DatosYConfirmacion from './DatosYConfirmacion';
import PantallaExito from './PantallaExito';
import VentanaReserva from './VentanaReserva';
import type { CitaCreada, DatosDelCliente, HoraDisponible, Servicio } from '@/tipos';

interface PropiedadesFlujoReserva {
    slug: string;
    direccion: string | null;
    servicio: Servicio;
    profesionalInicial?: number | null;
    onCerrar: () => void;
}

type Paso = 'ventana' | 'datos' | 'exito';

export default function FlujoReserva({ slug, direccion, servicio, profesionalInicial, onCerrar }: PropiedadesFlujoReserva): ReactElement {
    const hoy = useMemo(() => new Date(), []);
    const [paso, setPaso] = useState<Paso>('ventana');
    const [profesionalId, setProfesionalId] = useState<number | null>(profesionalInicial ?? null);
    const [anio, setAnio] = useState(hoy.getFullYear());
    const [mes, setMes] = useState(hoy.getMonth() + 1);
    const [diaSeleccionado, setDiaSeleccionado] = useState<string | null>(null);
    const [horaSeleccionada, setHoraSeleccionada] = useState<HoraDisponible | null>(null);
    const [citaCreada, setCitaCreada] = useState<CitaCreada | null>(null);

    useEffect(() => {
        function alPresionarTecla(evento: KeyboardEvent): void {
            if (evento.key === 'Escape') {
                onCerrar();
            }
        }

        document.addEventListener('keydown', alPresionarTecla);

        return () => document.removeEventListener('keydown', alPresionarTecla);
    }, [onCerrar]);

    const profesionalesQuery = useQuery({
        queryKey: ['profesionales', slug, servicio.id],
        queryFn: () => obtenerProfesionales(slug, servicio.id),
    });

    const mesTexto = `${anio}-${String(mes).padStart(2, '0')}`;
    const diasQuery = useQuery({
        queryKey: ['dias', slug, servicio.id, profesionalId, mesTexto],
        queryFn: () => obtenerDiasConDisponibilidad(slug, servicio.id, profesionalId, mesTexto),
    });

    const horasQuery = useQuery({
        queryKey: ['horas', slug, servicio.id, profesionalId, diaSeleccionado],
        queryFn: () => obtenerHorasDisponibles(slug, servicio.id, profesionalId, diaSeleccionado as string),
        enabled: diaSeleccionado !== null,
    });

    const mutacionReserva = useMutation({
        mutationFn: (datosDelCliente: DatosDelCliente) => {
            if (!horaSeleccionada) {
                throw new Error('No hay hora elegida.');
            }

            return crearReserva(slug, servicio.id, horaSeleccionada.profesionalId, horaSeleccionada.inicio, datosDelCliente);
        },
        onSuccess: (cita) => {
            setCitaCreada(cita);
            setPaso('exito');
        },
    });

    function cambiarMes(delta: number): void {
        const fecha = new Date(anio, mes - 1 + delta, 1);
        setAnio(fecha.getFullYear());
        setMes(fecha.getMonth() + 1);
        setDiaSeleccionado(null);
        setHoraSeleccionada(null);
    }

    const proximoDiaDisponible =
        (diasQuery.data ?? []).find((dia) => diaSeleccionado !== null && dia > diaSeleccionado) ?? null;

    const nombreProfesional = horaSeleccionada
        ? profesionalesQuery.data?.find((p) => p.id === horaSeleccionada.profesionalId)?.nombre ?? 'Cualquiera disponible'
        : 'Cualquiera disponible';

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/60 p-4">
            {paso === 'ventana' && (
                <VentanaReserva
                    servicio={servicio}
                    direccion={direccion}
                    profesionales={profesionalesQuery.data ?? []}
                    profesionalId={profesionalId}
                    onElegirProfesional={(id) => {
                        setProfesionalId(id);
                        setHoraSeleccionada(null);
                    }}
                    anio={anio}
                    mes={mes}
                    onCambiarMes={cambiarMes}
                    diasConDisponibilidad={diasQuery.data ?? []}
                    cargandoDias={diasQuery.isLoading}
                    diaSeleccionado={diaSeleccionado}
                    onSeleccionarDia={(dia) => {
                        setDiaSeleccionado(dia);
                        setHoraSeleccionada(null);
                    }}
                    horas={horasQuery.data ?? []}
                    cargandoHoras={horasQuery.isLoading}
                    horaSeleccionada={horaSeleccionada}
                    onSeleccionarHora={setHoraSeleccionada}
                    proximoDiaDisponible={proximoDiaDisponible}
                    onContinuar={() => setPaso('datos')}
                    onCerrar={onCerrar}
                />
            )}

            {paso === 'datos' && horaSeleccionada && (
                <DatosYConfirmacion
                    servicio={servicio}
                    nombreProfesional={nombreProfesional}
                    direccion={direccion}
                    fechaHoraVisible={horaSeleccionada.horaVisible}
                    enviando={mutacionReserva.isPending}
                    error={
                        mutacionReserva.error && esErrorDeReservaPublica(mutacionReserva.error)
                            ? mutacionReserva.error.message
                            : mutacionReserva.error
                              ? 'Algo falló. Inténtalo de nuevo.'
                              : null
                    }
                    onConfirmar={(datos) => mutacionReserva.mutate(datos)}
                    onVolver={() => setPaso('ventana')}
                />
            )}

            {paso === 'exito' && citaCreada && <PantallaExito cita={citaCreada} onCerrar={onCerrar} />}
        </div>
    );
}
