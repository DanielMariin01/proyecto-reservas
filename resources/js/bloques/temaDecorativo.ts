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
 * 25 posiciones fijas (compartidas por cualquier tema) donde dispersar
 * los íconos decorativos: tamaños grandes (40-72px) y cobertura por
 * todo el ancho/alto del fondo del banner, con rotaciones y
 * ubicaciones variadas para que no se vean en fila ni repetitivos.
 */
export const POSICIONES_DECORATIVAS: ReadonlyArray<{ top: string; left: string; size: number; rotate: number }> = [
    { top: '5%', left: '2%', size: 48, rotate: -20 },
    { top: '50%', left: '4%', size: 60, rotate: 15 },
    { top: '90%', left: '8%', size: 44, rotate: -10 },
    { top: '20%', left: '14%', size: 52, rotate: 25 },
    { top: '70%', left: '18%', size: 40, rotate: -30 },
    { top: '45%', left: '10%', size: 44, rotate: 22 },
    { top: '10%', left: '28%', size: 64, rotate: 10 },
    { top: '40%', left: '30%', size: 46, rotate: -15 },
    { top: '85%', left: '33%', size: 56, rotate: 20 },
    { top: '60%', left: '38%', size: 42, rotate: -5 },
    { top: '2%', left: '48%', size: 50, rotate: 30 },
    { top: '78%', left: '45%', size: 50, rotate: -18 },
    { top: '30%', left: '50%', size: 68, rotate: -25 },
    { top: '75%', left: '52%', size: 44, rotate: 15 },
    { top: '95%', left: '58%', size: 48, rotate: -12 },
    { top: '15%', left: '62%', size: 56, rotate: 8 },
    { top: '50%', left: '65%', size: 40, rotate: -30 },
    { top: '5%', left: '72%', size: 60, rotate: 18 },
    { top: '65%', left: '75%', size: 52, rotate: -8 },
    { top: '85%', left: '78%', size: 44, rotate: 25 },
    { top: '35%', left: '80%', size: 72, rotate: -20 },
    { top: '10%', left: '88%', size: 46, rotate: 12 },
    { top: '55%', left: '90%', size: 50, rotate: -15 },
    { top: '90%', left: '92%', size: 40, rotate: 30 },
    { top: '25%', left: '95%', size: 58, rotate: -5 },
];

export function obtenerIconosTema(valor: unknown): ComponentType<PropiedadesIconoTema>[] | null {
    if (typeof valor !== 'string' || valor === 'ninguno' || !(valor in ICONOS_POR_TEMA)) {
        return null;
    }

    return ICONOS_POR_TEMA[valor as Exclude<TemaDecorativo, 'ninguno'>];
}
