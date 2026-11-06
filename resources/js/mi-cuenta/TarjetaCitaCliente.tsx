import { Banknote, Building2, Calendar, Scissors, User } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Insignia from '@/componentes/Insignia';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import type { CitaDeCuenta } from '@/tipos';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

const ETIQUETAS_ESTADO: Record<(typeof ESTADOS_CON_INSIGNIA)[number], string> = {
    confirmada: 'Confirmada',
    pendiente: 'Pendiente',
    completada: 'Completada',
    en_atencion: 'En atención',
    cancelada: 'Cancelada',
    no_asistio: 'No asistió',
};

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface PropiedadesTarjetaCitaCliente {
    cita: CitaDeCuenta;
    onCancelar: () => void;
    onReprogramar: () => void;
    onDejarResena: () => void;
}

export default function TarjetaCitaCliente({ cita, onCancelar, onReprogramar, onDejarResena }: PropiedadesTarjetaCitaCliente): ReactElement {
    return (
        <Tarjeta>
            <div className="flex flex-wrap items-center justify-between gap-2">
                <h2 className="min-w-0 truncate text-base font-semibold text-tinta">{cita.nombreNegocio}</h2>
                {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={ETIQUETAS_ESTADO[cita.estado]} />}
            </div>

            <div className="mt-3 space-y-2">
                <LineaDetalle icono={Scissors} texto={cita.servicio} />
                <LineaDetalle icono={User} texto={cita.profesional} />
                <LineaDetalle icono={Calendar} texto={`${cita.fechaVisible}, ${cita.horaVisible}`} />
                {cita.direccion && <LineaDetalle icono={Building2} texto={cita.direccion} />}
                <LineaDetalle icono={Banknote} texto={`$${cita.precio.toLocaleString('es-CO')}`} />
            </div>

            {(cita.puedeCancelar || cita.puedeReprogramar || cita.puedeDejarResena) && (
                <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    {cita.puedeReprogramar && (
                        <Boton variante="contorno" onClick={onReprogramar} className="sm:w-auto">
                            Cambiar fecha u hora
                        </Boton>
                    )}
                    {cita.puedeCancelar && (
                        <Boton variante="contorno" onClick={onCancelar} className="sm:w-auto">
                            Cancelar
                        </Boton>
                    )}
                    {cita.puedeDejarResena && (
                        <Boton onClick={onDejarResena} className="sm:w-auto">
                            Dejar mi reseña
                        </Boton>
                    )}
                </div>
            )}

            {cita.resena && (
                <div className="mt-4 border-t border-gris-claro pt-3">
                    <p className="text-sm font-semibold text-tinta">Tu reseña: {cita.resena.calificacion} ★</p>
                    <p className="mt-1 text-sm text-texto-suave">&ldquo;{cita.resena.comentario}&rdquo;</p>
                    {cita.resena.respuestaTexto && (
                        <div className="mt-2 rounded-boton bg-fondo-panel p-3">
                            <p className="text-xs font-semibold text-tinta">Respuesta de {cita.nombreNegocio}</p>
                            <p className="mt-1 text-sm text-texto-suave">{cita.resena.respuestaTexto}</p>
                        </div>
                    )}
                </div>
            )}
        </Tarjeta>
    );
}
