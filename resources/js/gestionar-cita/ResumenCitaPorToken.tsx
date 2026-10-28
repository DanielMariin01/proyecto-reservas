import { Banknote, Building2, Calendar, MessageCircle, Scissors, User } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Insignia from '@/componentes/Insignia';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import type { CitaPorToken } from '@/tipos';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface PropiedadesResumenCitaPorToken {
    cita: CitaPorToken;
    onCancelar: () => void;
    onReprogramar: () => void;
}

export default function ResumenCitaPorToken({ cita, onCancelar, onReprogramar }: PropiedadesResumenCitaPorToken): ReactElement {
    const enlaceWhatsapp = cita.whatsapp
        ? `https://wa.me/${cita.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(
              `Hola, tengo una cita de ${cita.servicio} el ${cita.fechaVisible} a las ${cita.horaVisible} y necesito ayuda.`,
          )}`
        : null;

    return (
        <Tarjeta className="w-full max-w-md">
            <div className="flex items-center justify-between">
                <h1 className="text-xl font-semibold text-tinta">{cita.nombreNegocio}</h1>
                {esEstadoConInsignia(cita.estado) && <Insignia estado={cita.estado} texto={etiquetaEstado(cita.estado)} />}
            </div>

            <div className="mt-4 space-y-3">
                <LineaDetalle icono={Scissors} texto={cita.servicio} />
                <LineaDetalle icono={User} texto={cita.profesional} />
                <LineaDetalle icono={Calendar} texto={`${cita.fechaVisible}, ${cita.horaVisible}`} />
                {cita.direccion && <LineaDetalle icono={Building2} texto={cita.direccion} />}
                <LineaDetalle icono={Banknote} texto={`$${cita.precio.toLocaleString('es-CO')}`} />
            </div>

            {!cita.enlaceActivo && (
                <p className="mt-4 text-sm text-texto-suave">Esta cita ya no está activa, así que este enlace no permite hacer cambios.</p>
            )}

            {cita.enlaceActivo && (
                <div className="mt-6 flex flex-col gap-2">
                    {cita.puedeReprogramar && (
                        <Boton variante="principal" onClick={onReprogramar} className="w-full">
                            Cambiar fecha u hora
                        </Boton>
                    )}
                    {cita.puedeCancelar && (
                        <Boton variante="contorno" onClick={onCancelar} className="w-full">
                            Cancelar cita
                        </Boton>
                    )}
                    {(!cita.puedeCancelar || !cita.puedeReprogramar) && enlaceWhatsapp && (
                        <a href={enlaceWhatsapp} target="_blank" rel="noreferrer">
                            <Boton variante="contorno" className="w-full">
                                <span className="flex items-center justify-center gap-2">
                                    <MessageCircle size={18} /> Escribir por WhatsApp
                                </span>
                            </Boton>
                        </a>
                    )}
                </div>
            )}
        </Tarjeta>
    );
}

function etiquetaEstado(estado: string): string {
    const etiquetas: Record<string, string> = {
        confirmada: 'Confirmada',
        pendiente: 'Pendiente',
        cancelada: 'Cancelada',
        no_asistio: 'No asistió',
    };

    return etiquetas[estado] ?? estado;
}
