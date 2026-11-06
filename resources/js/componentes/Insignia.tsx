import type { ReactElement } from 'react';

type EstadoInsignia = 'confirmada' | 'pendiente' | 'cancelada' | 'no_asistio' | 'completada' | 'en_atencion';

interface PropiedadesInsignia {
    estado: EstadoInsignia;
    texto: string;
}

/**
 * `completada` y `en_atencion` no estaban en la skill original (pensada
 * para gestionar una cita por token, donde una completada ya no se
 * muestra). El panel "Mi cuenta" (T11) sí las necesita en su historial y
 * reusa los mismos colores que ya define `EstadoCitaValor::getColor()`
 * en el backend: completada = --secundario (como confirmada), en
 * atención = --primario.
 */
const CLASES_POR_ESTADO: Record<EstadoInsignia, string> = {
    confirmada: 'bg-secundario text-blanco',
    completada: 'bg-secundario text-blanco',
    pendiente: 'bg-acento text-blanco',
    en_atencion: 'bg-primario text-blanco',
    cancelada: 'bg-gris-claro text-tinta',
    no_asistio: 'bg-tinta text-blanco',
};

export default function Insignia({ estado, texto }: PropiedadesInsignia): ReactElement {
    return <span className={`rounded-boton px-2 py-1 text-xs font-semibold ${CLASES_POR_ESTADO[estado]}`}>{texto}</span>;
}
