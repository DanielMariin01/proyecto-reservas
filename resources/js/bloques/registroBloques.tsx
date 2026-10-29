import type { ComponentType } from 'react';
import BloqueBannerOferta from './BloqueBannerOferta';
import BloqueBarraPromocion from './BloqueBarraPromocion';
import BloqueEncabezado from './BloqueEncabezado';
import BloqueEquipo from './BloqueEquipo';
import BloqueGaleria from './BloqueGaleria';
import BloquePieDePagina from './BloquePieDePagina';
import BloquePortada from './BloquePortada';
import BloqueProximamente from './BloqueProximamente';
import BloqueServicios from './BloqueServicios';
import BloqueTestimonios from './BloqueTestimonios';
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
    barra_promocion: BloqueBarraPromocion,
    encabezado: BloqueEncabezado,
    portada: BloquePortada,
    equipo: BloqueEquipo,
    servicios: BloqueServicios,
    texto: BloqueTexto,
    texto_con_imagen: BloqueTextoConImagen,
    galeria: BloqueGaleria,
    banner_oferta: BloqueBannerOferta,
    testimonios: BloqueTestimonios,
    ubicacion: BloqueUbicacion,
    whatsapp: BloqueWhatsapp,
    pie_de_pagina: BloquePieDePagina,
    cursos: BloqueProximamente,
    productos: BloqueProximamente,
};

export default registroBloques;
