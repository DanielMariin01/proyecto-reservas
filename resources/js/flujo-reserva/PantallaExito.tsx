import { Banknote, Building2, Calendar, CalendarPlus, CheckCircle2, MapPin, MessageCircle, Scissors, User } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import type { CitaCreada } from '@/tipos';

interface PropiedadesPantallaExito {
    cita: CitaCreada;
    onCerrar: () => void;
}

function construirIcs(cita: CitaCreada): string {
    const aFormatoIcs = (iso: string): string => iso.replace(/[-:]/g, '').replace(/\.\d{3}/, '').replace('+00:00', 'Z');

    return [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'BEGIN:VEVENT',
        `DTSTART:${aFormatoIcs(cita.inicio)}`,
        `DTEND:${aFormatoIcs(cita.fin)}`,
        `SUMMARY:${cita.servicio} en ${cita.nombreNegocio}`,
        `LOCATION:${cita.direccion ?? ''}`,
        'END:VEVENT',
        'END:VCALENDAR',
    ].join('\r\n');
}

function descargarIcs(cita: CitaCreada): void {
    const blob = new Blob([construirIcs(cita)], { type: 'text/calendar' });
    const url = URL.createObjectURL(blob);
    const enlace = document.createElement('a');
    enlace.href = url;
    enlace.download = 'cita.ics';
    enlace.click();
    URL.revokeObjectURL(url);
}

export default function PantallaExito({ cita, onCerrar }: PropiedadesPantallaExito): ReactElement {
    const enlaceWhatsapp = cita.whatsapp
        ? `https://wa.me/${cita.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(
              `Hola, tengo una cita de ${cita.servicio} el ${cita.fechaVisible} a las ${cita.horaVisible}.`,
          )}`
        : null;
    const enlaceMapa = cita.direccion
        ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(cita.direccion)}`
        : null;

    return (
        <Tarjeta className="scroll-primario max-h-[90vh] w-full max-w-md overflow-y-auto text-center">
            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-secundario text-blanco">
                <CheckCircle2 size={32} />
            </div>
            <h2 className="text-xl font-semibold text-tinta">¡Tu cita quedó reservada!</h2>

            <div className="mt-4 space-y-2 rounded-tarjeta bg-fondo-panel p-4 text-left">
                <p className="font-semibold text-tinta">{cita.nombreNegocio}</p>
                <LineaDetalle icono={Scissors} texto={cita.servicio} />
                <LineaDetalle icono={User} texto={cita.profesional} />
                <LineaDetalle icono={Calendar} texto={`${cita.fechaVisible}, ${cita.horaVisible}`} />
                {cita.direccion && <LineaDetalle icono={Building2} texto={cita.direccion} />}
                <LineaDetalle icono={Banknote} texto={`$${cita.precio.toLocaleString('es-CO')}`} />
            </div>

            <div className="mt-4 flex flex-col gap-2">
                <Boton variante="contorno" onClick={() => descargarIcs(cita)}>
                    <span className="flex items-center justify-center gap-2">
                        <CalendarPlus size={18} /> Agregar a mi calendario
                    </span>
                </Boton>
                {enlaceWhatsapp && (
                    <a href={enlaceWhatsapp} target="_blank" rel="noreferrer">
                        <Boton variante="contorno" className="w-full">
                            <span className="flex items-center justify-center gap-2">
                                <MessageCircle size={18} /> Escribir por WhatsApp
                            </span>
                        </Boton>
                    </a>
                )}
                {enlaceMapa && (
                    <a href={enlaceMapa} target="_blank" rel="noreferrer">
                        <Boton variante="contorno" className="w-full">
                            <span className="flex items-center justify-center gap-2">
                                <MapPin size={18} /> Cómo llegar
                            </span>
                        </Boton>
                    </a>
                )}
            </div>

            <p className="mt-4 text-sm text-texto-suave">
                Te enviamos la confirmación a tu correo. Desde ahí puedes cancelar o cambiar la hora.
            </p>

            <Boton variante="texto" className="mt-2" onClick={onCerrar}>
                Cerrar
            </Boton>
        </Tarjeta>
    );
}
