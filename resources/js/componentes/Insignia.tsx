import type { ReactElement } from 'react';

type EstadoInsignia = 'confirmada' | 'pendiente' | 'cancelada' | 'no_asistio';

interface PropiedadesInsignia {
    estado: EstadoInsignia;
    texto: string;
}

const CLASES_POR_ESTADO: Record<EstadoInsignia, string> = {
    confirmada: 'bg-secundario text-blanco',
    pendiente: 'bg-acento text-blanco',
    cancelada: 'bg-gris-claro text-tinta',
    no_asistio: 'bg-tinta text-blanco',
};

export default function Insignia({ estado, texto }: PropiedadesInsignia): ReactElement {
    return <span className={`rounded-boton px-2 py-1 text-xs font-semibold ${CLASES_POR_ESTADO[estado]}`}>{texto}</span>;
}
