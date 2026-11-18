export interface Servicio {
    id: number;
    nombre: string;
    descripcion: string | null;
    categoria: string | null;
    categoriaServicioId: number | null;
    precio: number;
    duracionMinutos: number;
    foto: string | null;
}

export interface ServicioDestacado extends Servicio {
    puesto: number;
    totalReservas: number;
    clientesDistintos: number;
}

export interface Profesional {
    id: number;
    nombre: string;
    especialidad: string | null;
    biografia: string | null;
    color: string | null;
    foto: string | null;
    servicios: number[];
    notaPromedio: number | null;
    totalResenas: number;
}

export type FranjaHoraria = 'manana' | 'tarde' | 'noche';

export interface HoraDisponible {
    profesionalId: number;
    inicio: string;
    fin: string;
    fechaVisible: string;
    horaVisible: string;
    horaFinVisible: string;
    franja: FranjaHoraria;
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
    tipoEtiqueta: string;
    logo: string | null;
    colorPrimario: string | null;
    colorSecundario: string | null;
    direccion: string | null;
    whatsapp: string | null;
    redesSociales: Record<string, string>;
    bloques: Bloque[];
    esVistaPrevia: boolean;
}

export interface ResenaDeCita {
    calificacion: number;
    comentario: string;
    respuestaTexto: string | null;
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
    puedeDejarResena: boolean;
    resena: ResenaDeCita | null;
}

export interface CuentaCliente {
    nombre: string;
    correo: string;
}

export interface CitaDeCuenta extends CitaPorToken {
    id: number;
    negocioSlug: string;
    esProxima: boolean;
}

export interface DatosDelCliente {
    nombre: string;
    celular: string;
    correo: string;
    nota: string;
    autorizacionDatos: boolean;
}

export interface Producto {
    id: number;
    nombre: string;
    descripcion: string | null;
    foto: string | null;
    precio: number;
}

export interface ItemCarrito {
    producto: Producto;
    cantidad: number;
}

export interface ItemPedidoCreado {
    nombreProducto: string;
    precioUnitario: number;
    cantidad: number;
    subtotal: number;
}

export interface PedidoCreado {
    id: number;
    items: ItemPedidoCreado[];
    total: number;
    tipoEntrega: string;
    tipoEntregaEtiqueta: string;
    barrio: string | null;
    estado: string;
    direccion: string | null;
    whatsapp: string | null;
    nombreNegocio: string;
}

export interface DatosDelClienteCompra {
    nombre: string;
    celular: string;
    correo: string;
    nota: string;
    tipoEntrega: 'recoger' | 'domicilio';
    barrio: string;
    autorizacionDatos: boolean;
}
