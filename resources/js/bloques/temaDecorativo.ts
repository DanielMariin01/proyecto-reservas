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
 * 32 posiciones fijas (compartidas por cualquier tema) donde dispersar
 * los íconos decorativos: tamaños grandes (48-84px) y cobertura por
 * todo el ancho/alto del fondo del banner, con rotaciones y
 * ubicaciones variadas para que no se vean en fila ni repetitivos.
 */
export const POSICIONES_DECORATIVAS: ReadonlyArray<{ top: string; left: string; size: number; rotate: number }> = [
    { top: '5%', left: '1%', size: 56, rotate: -22 },
    { top: '35%', left: '3%', size: 64, rotate: 18 },
    { top: '65%', left: '2%', size: 50, rotate: -12 },
    { top: '90%', left: '5%', size: 60, rotate: 28 },
    { top: '15%', left: '10%', size: 72, rotate: -8 },
    { top: '50%', left: '9%', size: 54, rotate: 20 },
    { top: '78%', left: '12%', size: 68, rotate: -30 },
    { top: '5%', left: '20%', size: 58, rotate: 15 },
    { top: '30%', left: '22%', size: 76, rotate: -18 },
    { top: '60%', left: '20%', size: 52, rotate: 25 },
    { top: '85%', left: '24%', size: 64, rotate: -5 },
    { top: '18%', left: '32%', size: 60, rotate: 30 },
    { top: '45%', left: '30%', size: 84, rotate: -25 },
    { top: '70%', left: '33%', size: 56, rotate: 10 },
    { top: '95%', left: '35%', size: 50, rotate: -15 },
    { top: '8%', left: '42%', size: 68, rotate: 22 },
    { top: '38%', left: '44%', size: 58, rotate: -10 },
    { top: '62%', left: '46%', size: 72, rotate: 18 },
    { top: '88%', left: '48%', size: 54, rotate: -28 },
    { top: '22%', left: '55%', size: 64, rotate: 12 },
    { top: '48%', left: '58%', size: 80, rotate: -20 },
    { top: '75%', left: '56%', size: 56, rotate: 25 },
    { top: '5%', left: '65%', size: 60, rotate: -8 },
    { top: '32%', left: '68%', size: 52, rotate: 15 },
    { top: '58%', left: '70%', size: 68, rotate: -30 },
    { top: '82%', left: '72%', size: 60, rotate: 20 },
    { top: '15%', left: '80%', size: 72, rotate: -12 },
    { top: '42%', left: '82%', size: 54, rotate: 28 },
    { top: '68%', left: '85%', size: 64, rotate: -18 },
    { top: '92%', left: '88%', size: 48, rotate: 10 },
    { top: '25%', left: '92%', size: 58, rotate: -25 },
    { top: '55%', left: '95%', size: 66, rotate: 15 },
];

export function obtenerIconosTema(valor: unknown): ComponentType<PropiedadesIconoTema>[] | null {
    if (typeof valor !== 'string' || valor === 'ninguno' || !(valor in ICONOS_POR_TEMA)) {
        return null;
    }

    return ICONOS_POR_TEMA[valor as Exclude<TemaDecorativo, 'ninguno'>];
}
