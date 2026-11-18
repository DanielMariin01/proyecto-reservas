import { Calendar, CalendarX, CheckCircle2, Clock } from 'lucide-react';
import type { ComponentType, ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerEstadisticas } from './api/estadisticas';

interface PropiedadesTarjetaMetrica {
    etiqueta: string;
    valor: number;
    Icono: ComponentType<{ size?: number; strokeWidth?: number; className?: string }>;
    colorFondo: string;
    colorIcono: string;
}

function TarjetaMetrica({ etiqueta, valor, Icono, colorFondo, colorIcono }: PropiedadesTarjetaMetrica): ReactElement {
    return (
        <Tarjeta>
            <div className={`mb-2 flex h-9 w-9 items-center justify-center rounded-full ${colorFondo}`}>
                <Icono size={18} strokeWidth={1.75} className={colorIcono} />
            </div>
            <p className="text-xs text-texto-suave">{etiqueta}</p>
            <p className="text-xl font-bold text-tinta">{valor}</p>
        </Tarjeta>
    );
}

export default function TarjetasEstadisticas(): ReactElement {
    const consulta = useQuery({ queryKey: ['mi-cuenta-estadisticas'], queryFn: obtenerEstadisticas });

    if (consulta.isLoading) {
        return (
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                {[0, 1, 2, 3].map((indice) => (
                    <div key={indice} className="h-24 animate-pulse rounded-tarjeta bg-gris-claro" />
                ))}
            </div>
        );
    }

    if (consulta.isError || !consulta.data) {
        return <></>;
    }

    const datos = consulta.data;

    return (
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <TarjetaMetrica etiqueta="Citas totales" valor={datos.citasTotales} Icono={Calendar} colorFondo="bg-primario/15" colorIcono="text-primario" />
            <TarjetaMetrica etiqueta="Pendientes" valor={datos.citasPendientes} Icono={Clock} colorFondo="bg-acento/15" colorIcono="text-acento" />
            <TarjetaMetrica
                etiqueta="Completadas"
                valor={datos.citasCompletadas}
                Icono={CheckCircle2}
                colorFondo="bg-secundario/15"
                colorIcono="text-secundario"
            />
            <TarjetaMetrica etiqueta="Canceladas" valor={datos.citasCanceladas} Icono={CalendarX} colorFondo="bg-gris-claro" colorIcono="text-texto-suave" />
        </div>
    );
}
