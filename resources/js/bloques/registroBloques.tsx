import type { ComponentType } from 'react';
import BloqueEquipo from './BloqueEquipo';
import BloqueGaleria from './BloqueGaleria';
import BloquePortada from './BloquePortada';
import BloqueProximamente from './BloqueProximamente';
import BloqueServicios from './BloqueServicios';
import BloqueTexto from './BloqueTexto';
import BloqueTextoConImagen from './BloqueTextoConImagen';
import BloqueUbicacion from './BloqueUbicacion';
import BloqueWhatsapp from './BloqueWhatsapp';
import type { PropiedadesBloque } from './tipos';

/**
 * RF-BLQ-04: un registro de componentes por tipo de bloque. Agregar un
 * tipo nuevo (cuando cursos/productos tengan datos reales, por ejemplo)
 * es agregar una entrada aquí, sin tocar las demás.
 */
const registroBloques: Record<string, ComponentType<PropiedadesBloque>> = {
    portada: BloquePortada,
    equipo: BloqueEquipo,
    servicios: BloqueServicios,
    texto: BloqueTexto,
    texto_con_imagen: BloqueTextoConImagen,
    galeria: BloqueGaleria,
    ubicacion: BloqueUbicacion,
    whatsapp: BloqueWhatsapp,
    cursos: BloqueProximamente,
    productos: BloqueProximamente,
};

export default registroBloques;
