import axios from 'axios';
import type { FranjaHoraria } from '@/tipos';

export interface CitaDelDia {
    id: number;
    hora: string;
    clienteNombre: string | null;
    servicioNombre: string | null;
    profesionalNombre: string | null;
    estado: string;
    estadoEtiqueta: string;
}

export interface BloqueLibreFranja {
    franja: FranjaHoraria;
    texto: string;
}

export interface HuecosLibresDeProfesional {
    profesionalId: number;
    profesionalNombre: string;
    bloques: BloqueLibreFranja[];
}

export interface PanelDelDia {
    totalCitasHoy: number;
    totalProximas: number;
    ingresosRegistrados: number;
    citas: CitaDelDia[];
    huecosLibres: HuecosLibresDeProfesional[];
}

export async function obtenerPanelDelDia(slug: string): Promise<PanelDelDia> {
    const respuesta = await axios.get<PanelDelDia>(`/app/${slug}/api/panel-del-dia`);

    return respuesta.data;
}
