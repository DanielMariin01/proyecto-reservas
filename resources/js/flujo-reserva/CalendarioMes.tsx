import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';

interface PropiedadesCalendarioMes {
    anio: number;
    mes: number; // 1-12
    diasConDisponibilidad: string[];
    diaSeleccionado: string | null;
    onSeleccionarDia: (dia: string) => void;
    onCambiarMes: (delta: number) => void;
    cargando: boolean;
}

const DIAS_SEMANA = ['lun.', 'mar.', 'mié.', 'jue.', 'vie.', 'sáb.', 'dom.'];
const NOMBRES_MES = [
    'enero',
    'febrero',
    'marzo',
    'abril',
    'mayo',
    'junio',
    'julio',
    'agosto',
    'septiembre',
    'octubre',
    'noviembre',
    'diciembre',
];

function aTextoFecha(anio: number, mes: number, dia: number): string {
    return `${anio}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
}

export default function CalendarioMes({
    anio,
    mes,
    diasConDisponibilidad,
    diaSeleccionado,
    onSeleccionarDia,
    onCambiarMes,
    cargando,
}: PropiedadesCalendarioMes): ReactElement {
    const hoyTexto = aTextoFecha(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate());
    const primerDiaSemana = (new Date(anio, mes - 1, 1).getDay() + 6) % 7; // 0 = lunes
    const diasEnElMes = new Date(anio, mes, 0).getDate();
    const celdas: Array<{ texto: string; dia: number } | null> = [
        ...Array.from({ length: primerDiaSemana }, () => null),
        ...Array.from({ length: diasEnElMes }, (_, i) => ({ texto: aTextoFecha(anio, mes, i + 1), dia: i + 1 })),
    ];

    return (
        <Tarjeta>
            <div className="mb-4 flex items-center justify-between">
                <button
                    type="button"
                    onClick={() => onCambiarMes(-1)}
                    aria-label="Mes anterior"
                    className="flex h-8 w-8 items-center justify-center rounded-boton text-tinta hover:bg-fondo-panel"
                >
                    ‹
                </button>
                <span className="font-semibold capitalize text-tinta">
                    {NOMBRES_MES[mes - 1]} {anio}
                </span>
                <button
                    type="button"
                    onClick={() => onCambiarMes(1)}
                    aria-label="Mes siguiente"
                    className="flex h-8 w-8 items-center justify-center rounded-boton text-tinta hover:bg-fondo-panel"
                >
                    ›
                </button>
            </div>

            <div className="grid grid-cols-7 gap-1 text-center text-xs text-texto-suave">
                {DIAS_SEMANA.map((nombre) => (
                    <span key={nombre}>{nombre}</span>
                ))}
            </div>

            <div className="mt-1 grid grid-cols-7 gap-1" aria-busy={cargando}>
                {celdas.map((celda, indice) => {
                    if (!celda) {
                        return <span key={`vacio-${indice}`} />;
                    }

                    const disponible = diasConDisponibilidad.includes(celda.texto);
                    const esPasado = celda.texto < hoyTexto;
                    const esHoy = celda.texto === hoyTexto;
                    const seleccionado = celda.texto === diaSeleccionado;
                    const sePuedeElegir = disponible && !esPasado;

                    return (
                        <button
                            key={celda.texto}
                            type="button"
                            disabled={!sePuedeElegir}
                            onClick={() => onSeleccionarDia(celda.texto)}
                            className={`relative flex h-10 w-10 items-center justify-center rounded-full text-sm ${
                                seleccionado
                                    ? 'border-2 border-primario bg-celeste font-semibold text-tinta'
                                    : sePuedeElegir
                                      ? 'text-tinta hover:bg-fondo-panel'
                                      : 'text-texto line-through'
                            }`}
                        >
                            {celda.dia}
                            {esHoy && <span className="absolute bottom-1 h-1 w-1 rounded-full bg-primario" />}
                        </button>
                    );
                })}
            </div>
        </Tarjeta>
    );
}
