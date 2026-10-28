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
    horaVisible: string;
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

export interface DatosDelCliente {
    nombre: string;
    celular: string;
    correo: string;
    nota: string;
    autorizacionDatos: boolean;
}
