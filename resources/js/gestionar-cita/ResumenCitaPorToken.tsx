import { Banknote, Building2, Calendar, MessageCircle, Scissors, Star, User } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import Insignia from '@/componentes/Insignia';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import type { CitaPorToken } from '@/tipos';

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio'] as const;
const MAXIMO_ESTRELLAS = 5;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface PropiedadesResumenCitaPorToken {
    cita: CitaPorToken;
    onCancelar: () => void;
    onReprogramar: () => void;
    onDejarResena: () => void;
}

export default function ResumenCitaPorToken({ cita, onCancelar, onReprogramar, onDejarResena }: PropiedadesResumenCitaPorToken): ReactElement {
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
                    {cita.puedeDejarResena && (
                        <Boton variante="contorno" onClick={onDejarResena} className="w-full">
                            Dejar mi reseña
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

            {cita.resena && (
                <div className="mt-6 border-t border-gris-claro pt-4">
                    <p className="text-sm font-semibold text-tinta">Tu reseña</p>
                    <div className="mt-2 flex gap-1">
                        {Array.from({ length: MAXIMO_ESTRELLAS }, (_valor, indice) => (
                            <Star
                                key={indice}
                                size={16}
                                strokeWidth={1.75}
                                className={indice < cita.resena!.calificacion ? 'fill-secundario text-secundario' : 'text-gris-claro'}
                            />
                        ))}
                    </div>
                    <p className="mt-2 text-sm text-texto-suave">&ldquo;{cita.resena.comentario}&rdquo;</p>
                    {cita.resena.respuestaTexto && (
                        <div className="mt-3 rounded-boton bg-fondo-panel p-3">
                            <p className="text-xs font-semibold text-tinta">Respuesta de {cita.nombreNegocio}</p>
                            <p className="mt-1 text-sm text-texto-suave">{cita.resena.respuestaTexto}</p>
                        </div>
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
