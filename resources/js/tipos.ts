export interface Servicio {
    id: number;
    nombre: string;
    descripcion: string | null;
    categoria: string | null;
    precio: number;
    duracionMinutos: number;
    foto: string | null;
}

export interface Profesional {
    id: number;
    nombre: string;
    especialidad: string | null;
    biografia: string | null;
    color: string | null;
    foto: string | null;
    servicios: number[];
}

export interface HoraDisponible {
    profesionalId: number;
    inicio: string;
    fin: string;
    fechaVisible: string;
    horaVisible: string;
    horaFinVisible: string;
}

export interface CitaCreada {
    id: number;
    servicio: string;
    profesional: string;
    cliente: string;
    inicio: string;
    fin: string;
    fechaVisible: string;
    horaVisible: string;
    precio: number;
    estado: string;
    direccion: string | null;
    whatsapp: string | null;
    nombreNegocio: string;
}

export interface ErrorReserva {
    mensaje: string;
    horasCercanas: string[];
}

export interface Bloque {
    tipo: string;
    datos: Record<string, unknown>;
}

export interface PaginaPublica {
    nombreNegocio: string;
    tipo: string;
    logo: string | null;
    colorPrimario: string | null;
    colorSecundario: string | null;
    direccion: string | null;
    whatsapp: string | null;
    redesSociales: Record<string, string>;
    bloques: Bloque[];
    esVistaPrevia: boolean;
}

export interface CitaPorToken {
    servicioId: number;
    profesionalId: number;
    servicio: string;
    profesional: string;
    inicio: string;
    fin: string;
    fechaVisible: string;
    horaVisible: string;
    precio: number;
    estado: string;
    direccion: string | null;
    whatsapp: string | null;
    nombreNegocio: string;
    enlaceActivo: boolean;
    puedeCancelar: boolean;
    puedeReprogramar: boolean;
}

export interface DatosDelCliente {
    nombre: string;
    celular: string;
    correo: string;
    nota: string;
    autorizacionDatos: boolean;
}
