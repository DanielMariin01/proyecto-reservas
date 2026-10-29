import { useEffect, useMemo, useState } from 'react';
import type { ReactElement } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import {
    crearReserva,
    esErrorDeReservaPublica,
    obtenerDiasConDisponibilidad,
    obtenerHorasDisponibles,
    obtenerProfesionales,
    obtenerServicios,
} from './api/clientePublico';
import ConfirmarResumen from './ConfirmarResumen';
import DatosYConfirmacion from './DatosYConfirmacion';
import PantallaExito from './PantallaExito';
import SeleccionarProfesional from './SeleccionarProfesional';
import SeleccionarServicio from './SeleccionarServicio';
import VentanaReserva from './VentanaReserva';
import type { CitaCreada, DatosDelCliente, HoraDisponible, Servicio } from '@/tipos';

interface PropiedadesFlujoReserva {
    slug: string;
    direccion: string | null;
    /** Si no se pasa (p. ej. "Reservar" desde la portada), el primer paso pide elegir servicio. */
    servicioInicial?: Servicio | null;
    profesionalInicial?: number | null;
    onCerrar: () => void;
}

type Paso = 'servicio' | 'profesional' | 'ventana' | 'datos' | 'confirmar' | 'exito';

const CUALQUIERA_DISPONIBLE = 'Cualquiera disponible';

function pasoInicial(servicioInicial: Servicio | null | undefined, profesionalInicial: number | null | undefined): Paso {
    if (!servicioInicial) {
        return 'servicio';
    }

    return profesionalInicial != null ? 'ventana' : 'profesional';
}

export default function FlujoReserva({ slug, direccion, servicioInicial, profesionalInicial, onCerrar }: PropiedadesFlujoReserva): ReactElement {
    const hoy = useMemo(() => new Date(), []);
    const [paso, setPaso] = useState<Paso>(pasoInicial(servicioInicial, profesionalInicial));
    const [servicio, setServicio] = useState<Servicio | null>(servicioInicial ?? null);
    const [profesionalId, setProfesionalId] = useState<number | null>(profesionalInicial ?? null);
    const [anio, setAnio] = useState(hoy.getFullYear());
    const [mes, setMes] = useState(hoy.getMonth() + 1);
    const [diaSeleccionado, setDiaSeleccionado] = useState<string | null>(null);
    const [horaSeleccionada, setHoraSeleccionada] = useState<HoraDisponible | null>(null);
    const [datosDelCliente, setDatosDelCliente] = useState<DatosDelCliente | null>(null);
    const [citaCreada, setCitaCreada] = useState<CitaCreada | null>(null);

    const serviciosQuery = useQuery({
        queryKey: ['servicios', slug],
        queryFn: () => obtenerServicios(slug),
        enabled: paso === 'servicio',
    });

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
        queryKey: ['profesionales', slug, servicio?.id],
        queryFn: () => obtenerProfesionales(slug, (servicio as Servicio).id),
        enabled: servicio !== null,
    });

    const mesTexto = `${anio}-${String(mes).padStart(2, '0')}`;
    const diasQuery = useQuery({
        queryKey: ['dias', slug, servicio?.id, profesionalId, mesTexto],
        queryFn: () => obtenerDiasConDisponibilidad(slug, (servicio as Servicio).id, profesionalId, mesTexto),
        enabled: servicio !== null && paso !== 'profesional',
    });

    const horasQuery = useQuery({
        queryKey: ['horas', slug, servicio?.id, profesionalId, diaSeleccionado],
        queryFn: () => obtenerHorasDisponibles(slug, (servicio as Servicio).id, profesionalId, diaSeleccionado as string),
        enabled: servicio !== null && diaSeleccionado !== null,
    });

    const mutacionReserva = useMutation({
        mutationFn: () => {
            if (!servicio || !horaSeleccionada || !datosDelCliente) {
                throw new Error('Faltan datos para reservar.');
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

    function nombrePorId(id: number | null): string {
        if (id === null) {
            return CUALQUIERA_DISPONIBLE;
        }

        return profesionalesQuery.data?.find((p) => p.id === id)?.nombre ?? CUALQUIERA_DISPONIBLE;
    }

    const proximoDiaDisponible =
        (diasQuery.data ?? []).find((dia) => diaSeleccionado !== null && dia > diaSeleccionado) ?? null;

    const errorDeReserva =
        mutacionReserva.error && esErrorDeReservaPublica(mutacionReserva.error)
            ? mutacionReserva.error.message
            : mutacionReserva.error
              ? 'Algo falló. Inténtalo de nuevo.'
              : null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/60 p-4">
            {paso === 'servicio' && (
                <SeleccionarServicio
                    servicios={serviciosQuery.data ?? []}
                    cargando={serviciosQuery.isLoading}
                    onElegir={(servicioElegido) => {
                        setServicio(servicioElegido);
                        setPaso(profesionalInicial != null ? 'ventana' : 'profesional');
                    }}
                    onCerrar={onCerrar}
                />
            )}

            {paso === 'profesional' && servicio && (
                <SeleccionarProfesional
                    servicio={servicio}
                    profesionales={profesionalesQuery.data ?? []}
                    cargando={profesionalesQuery.isLoading}
                    onElegir={(id) => {
                        setProfesionalId(id);
                        setPaso('ventana');
                    }}
                    onCerrar={onCerrar}
                />
            )}

            {paso === 'ventana' && servicio && (
                <VentanaReserva
                    servicio={servicio}
                    direccion={direccion}
                    nombreProfesional={nombrePorId(profesionalId)}
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

            {paso === 'datos' && servicio && horaSeleccionada && (
                <DatosYConfirmacion
                    servicio={servicio}
                    nombreProfesional={nombrePorId(horaSeleccionada.profesionalId)}
                    direccion={direccion}
                    fechaHoraVisible={`${horaSeleccionada.fechaVisible}, ${horaSeleccionada.horaVisible} – ${horaSeleccionada.horaFinVisible}`}
                    valoresIniciales={datosDelCliente}
                    onContinuar={(datos) => {
                        setDatosDelCliente(datos);
                        setPaso('confirmar');
                    }}
                    onVolver={() => setPaso('ventana')}
                />
            )}

            {paso === 'confirmar' && servicio && horaSeleccionada && datosDelCliente && (
                <ConfirmarResumen
                    nombreProfesional={nombrePorId(horaSeleccionada.profesionalId)}
                    horaVisible={horaSeleccionada.horaVisible}
                    nombreServicio={servicio.nombre}
                    precio={servicio.precio}
                    enviando={mutacionReserva.isPending}
                    error={errorDeReserva}
                    onConfirmar={() => mutacionReserva.mutate()}
                    onVolver={() => setPaso('datos')}
                />
            )}

            {paso === 'exito' && citaCreada && <PantallaExito cita={citaCreada} onCerrar={onCerrar} />}
        </div>
    );
}
