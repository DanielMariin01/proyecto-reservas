import { Building2, Clock, User, X } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import Boton from '@/componentes/Boton';
import LineaDetalle from '@/componentes/LineaDetalle';
import SelectorDeHora from '@/componentes/SelectorDeHora';
import SelectorFranjaHoraria from '@/componentes/SelectorFranjaHoraria';
import type { FranjaHoraria, HoraDisponible, Servicio } from '@/tipos';
import CalendarioMes from './CalendarioMes';

interface PropiedadesVentanaReserva {
    servicio: Servicio;
    direccion: string | null;
    nombreProfesional: string;
    anio: number;
    mes: number;
    onCambiarMes: (delta: number) => void;
    diasConDisponibilidad: string[];
    cargandoDias: boolean;
    diaSeleccionado: string | null;
    onSeleccionarDia: (dia: string) => void;
    horas: HoraDisponible[];
    cargandoHoras: boolean;
    horaSeleccionada: HoraDisponible | null;
    onSeleccionarHora: (hora: HoraDisponible) => void;
    proximoDiaDisponible: string | null;
    onContinuar: () => void;
    onCerrar: () => void;
}

export default function VentanaReserva({
    servicio,
    direccion,
    nombreProfesional,
    anio,
    mes,
    onCambiarMes,
    diasConDisponibilidad,
    cargandoDias,
    diaSeleccionado,
    onSeleccionarDia,
    horas,
    cargandoHoras,
    horaSeleccionada,
    onSeleccionarHora,
    proximoDiaDisponible,
    onContinuar,
    onCerrar,
}: PropiedadesVentanaReserva): ReactElement {
    const [franjaSeleccionada, setFranjaSeleccionada] = useState<FranjaHoraria>('manana');
    const horasEnFranja = horas.filter((hora) => hora.franja === franjaSeleccionada);

    return (
        <div className="relative flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-tarjeta bg-blanco sm:flex-row">
            <button
                type="button"
                onClick={onCerrar}
                aria-label="Cerrar"
                className="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-tinta text-blanco transition-colors hover:bg-tinta/80"
            >
                <X size={18} />
            </button>

            <div className="w-full shrink-0 bg-blanco p-6 sm:w-[35%]">
                <h2 className="text-xl font-semibold text-tinta">{servicio.nombre}</h2>
                <div className="mt-3 space-y-2">
                    <LineaDetalle icono={Clock} texto={`${servicio.duracionMinutos} min`} />
                    <LineaDetalle icono={User} texto={nombreProfesional} />
                    {direccion && <LineaDetalle icono={Building2} texto={direccion} />}
                </div>
                <p className="mt-3 text-lg font-bold text-tinta">${servicio.precio.toLocaleString('es-CO')}</p>
            </div>

            <div className="flex min-h-0 w-full flex-1 flex-col bg-fondo-panel sm:w-[65%]">
                <div className="scroll-primario min-h-0 flex-1 overflow-y-auto p-6">
                    <h3 className="mb-4 text-xl font-semibold text-tinta">Selecciona fecha y hora</h3>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <CalendarioMes
                            anio={anio}
                            mes={mes}
                            diasConDisponibilidad={diasConDisponibilidad}
                            diaSeleccionado={diaSeleccionado}
                            onSeleccionarDia={onSeleccionarDia}
                            onCambiarMes={onCambiarMes}
                            cargando={cargandoDias}
                        />

                        <div>
                            <p className="text-sm font-semibold text-tinta">Elige un horario disponible</p>
                            <p className="mb-3 text-xs text-texto-suave">(GMT-5) Bogotá</p>

                            {cargandoHoras && (
                                <div className="space-y-2" aria-label="Cargando horas">
                                    {[1, 2, 3].map((n) => (
                                        <div key={n} className="h-11 animate-pulse rounded-boton bg-gris-claro" />
                                    ))}
                                </div>
                            )}

                            {!cargandoHoras && diaSeleccionado && horas.length === 0 && (
                                <div className="rounded-tarjeta bg-blanco p-4 text-center">
                                    <p className="text-sm text-texto-suave">No hay horas este día</p>
                                    {proximoDiaDisponible && (
                                        <Boton variante="texto" className="mt-2 h-auto p-0" onClick={() => onSeleccionarDia(proximoDiaDisponible)}>
                                            Ver próximo día disponible
                                        </Boton>
                                    )}
                                </div>
                            )}

                            {!cargandoHoras && !diaSeleccionado && <p className="text-sm text-texto-suave">Elige un día en el calendario.</p>}

                            {!cargandoHoras && horas.length > 0 && (
                                <>
                                    <div className="mb-3">
                                        <SelectorFranjaHoraria franjaSeleccionada={franjaSeleccionada} onSeleccionar={setFranjaSeleccionada} />
                                    </div>

                                    {horasEnFranja.length === 0 ? (
                                        <p className="text-sm text-texto-suave">No hay horas en esta franja.</p>
                                    ) : (
                                        <div className="space-y-2">
                                            {horasEnFranja.map((hora) => (
                                                <SelectorDeHora
                                                    key={`${hora.profesionalId}-${hora.inicio}`}
                                                    texto={hora.horaVisible}
                                                    seleccionado={
                                                        horaSeleccionada?.inicio === hora.inicio &&
                                                        horaSeleccionada?.profesionalId === hora.profesionalId
                                                    }
                                                    onElegir={() => onSeleccionarHora(hora)}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex justify-end gap-3 border-t border-gris-claro p-6 pt-4">
                    <Boton variante="contorno" onClick={onCerrar}>
                        Cancelar
                    </Boton>
                    <Boton onClick={onContinuar} disabled={!horaSeleccionada}>
                        Continuar
                    </Boton>
                </div>
            </div>
        </div>
    );
}
