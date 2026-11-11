import {
    Columns3,
    FileText,
    GraduationCap,
    Image,
    Images,
    LayoutGrid,
    MapPin,
    Megaphone,
    Menu,
    MessageCircle,
    MessageSquareQuote,
    PanelBottom,
    ShoppingBag,
    Sparkles,
    Tag,
    Users,
} from 'lucide-react';
import type { ComponentType } from 'react';

interface PropiedadesIcono {
    size?: number;
    strokeWidth?: number;
    className?: string;
}

export interface MetaTipoBloque {
    tipo: string;
    etiqueta: string;
    Icono: ComponentType<PropiedadesIcono>;
    disponible: boolean;
}

/**
 * Espejo de App\Enums\TipoBloque (etiqueta y disponible) + un ícono
 * Lucide para identificar cada tarjeta de bloque en el Builder sin
 * tener que expandirla, igual que hacía el Builder de Filament con un
 * ícono Heroicon por tipo (skill diseno-reservas, sección 7).
 */
export const TIPOS_BLOQUE: MetaTipoBloque[] = [
    { tipo: 'barra_promocion', etiqueta: 'Barra de promoción', Icono: Megaphone, disponible: true },
    { tipo: 'encabezado', etiqueta: 'Encabezado', Icono: Menu, disponible: true },
    { tipo: 'portada', etiqueta: 'Portada', Icono: Image, disponible: true },
    { tipo: 'equipo', etiqueta: 'Nuestro equipo', Icono: Users, disponible: true },
    { tipo: 'servicios', etiqueta: 'Servicios', Icono: Sparkles, disponible: true },
    { tipo: 'cursos', etiqueta: 'Cursos (próximamente)', Icono: GraduationCap, disponible: false },
    { tipo: 'productos', etiqueta: 'Productos (próximamente)', Icono: ShoppingBag, disponible: false },
    { tipo: 'texto', etiqueta: 'Texto', Icono: FileText, disponible: true },
    { tipo: 'texto_con_imagen', etiqueta: 'Texto con imagen', Icono: Columns3, disponible: true },
    { tipo: 'galeria', etiqueta: 'Galería', Icono: Images, disponible: true },
    { tipo: 'tarjetas', etiqueta: 'Tarjetas en columnas', Icono: LayoutGrid, disponible: true },
    { tipo: 'banner_oferta', etiqueta: 'Banner de oferta', Icono: Tag, disponible: true },
    { tipo: 'testimonios', etiqueta: 'Testimonios', Icono: MessageSquareQuote, disponible: true },
    { tipo: 'ubicacion', etiqueta: 'Ubicación', Icono: MapPin, disponible: true },
    { tipo: 'whatsapp', etiqueta: 'WhatsApp flotante', Icono: MessageCircle, disponible: true },
    { tipo: 'pie_de_pagina', etiqueta: 'Pie de página', Icono: PanelBottom, disponible: true },
];

export function metaDelTipo(tipo: string): MetaTipoBloque {
    return TIPOS_BLOQUE.find((meta) => meta.tipo === tipo) ?? TIPOS_BLOQUE[0];
}
