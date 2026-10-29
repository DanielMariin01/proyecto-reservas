import { Balloon, Bug, Flower, Flower2, Ghost, Gift, Heart, HeartHandshake, PartyPopper, Skull, Snowflake, Stars, TreePine } from 'lucide-react';
import type { ComponentType, CSSProperties } from 'react';

export type TemaDecorativo = 'ninguno' | 'halloween' | 'navidad' | 'amor_amistad' | 'anio_nuevo' | 'dia_madre';

interface PropiedadesIconoTema {
    size?: number;
    strokeWidth?: number;
    fill?: string;
    className?: string;
    style?: CSSProperties;
}

/**
 * 3 íconos de Lucide por tema, verificados que existen en el paquete
 * (no hay `spider` ni `pumpkin`, por eso Halloween usa `bug`/`ghost`/
 * `skull`). Activación manual del super admin — nunca por fecha del
 * sistema.
 */
const ICONOS_POR_TEMA: Record<Exclude<TemaDecorativo, 'ninguno'>, ComponentType<PropiedadesIconoTema>[]> = {
    halloween: [Ghost, Bug, Skull],
    navidad: [TreePine, Gift, Snowflake],
    amor_amistad: [Heart, HeartHandshake, Gift],
    anio_nuevo: [PartyPopper, Balloon, Stars],
    dia_madre: [Flower, Flower2, Heart],
};

/**
 * 16 posiciones fijas (compartidas por cualquier tema) donde dispersar
 * los íconos decorativos: tamaños notablemente más grandes (28-56px) y
 * cobertura por todo el ancho/alto del fondo del banner, con rotaciones
 * y ubicaciones variadas para que no se vean en fila ni repetitivos.
 */
export const POSICIONES_DECORATIVAS: ReadonlyArray<{ top: string; left: string; size: number; rotate: number }> = [
    { top: '8%', left: '3%', size: 36, rotate: -15 },
    { top: '65%', left: '6%', size: 48, rotate: 20 },
    { top: '15%', left: '18%', size: 28, rotate: 10 },
    { top: '80%', left: '22%', size: 40, rotate: -25 },
    { top: '5%', left: '35%', size: 32, rotate: 15 },
    { top: '45%', left: '40%', size: 52, rotate: -10 },
    { top: '85%', left: '48%', size: 30, rotate: 25 },
    { top: '20%', left: '55%', size: 44, rotate: -20 },
    { top: '60%', left: '62%', size: 36, rotate: 30 },
    { top: '10%', left: '70%', size: 48, rotate: -8 },
    { top: '75%', left: '75%', size: 32, rotate: 18 },
    { top: '35%', left: '82%', size: 56, rotate: -30 },
    { top: '5%', left: '90%', size: 28, rotate: 12 },
    { top: '55%', left: '95%', size: 40, rotate: -15 },
    { top: '90%', left: '88%', size: 34, rotate: 22 },
    { top: '30%', left: '10%', size: 44, rotate: -5 },
];

export function obtenerIconosTema(valor: unknown): ComponentType<PropiedadesIconoTema>[] | null {
    if (typeof valor !== 'string' || valor === 'ninguno' || !(valor in ICONOS_POR_TEMA)) {
        return null;
    }

    return ICONOS_POR_TEMA[valor as Exclude<TemaDecorativo, 'ninguno'>];
}
