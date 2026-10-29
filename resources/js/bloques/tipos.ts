import type { PaginaPublica, Servicio } from '@/tipos';

export interface OpcionesReserva {
    servicio?: Servicio;
    profesionalId?: number;
}

export interface PropiedadesBloque {
    tipo: string;
    datos: Record<string, unknown>;
    pagina: PaginaPublica;
    slug: string;
    onReservar: (opciones: OpcionesReserva) => void;
}
