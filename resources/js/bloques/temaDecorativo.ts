import { Balloon, Bug, Flower, Flower2, Ghost, Gift, Heart, HeartHandshake, PartyPopper, Skull, Snowflake, Stars, TreePine } from 'lucide-react';
import type { ComponentType, CSSProperties } from 'react';

export type TemaDecorativo = 'ninguno' | 'halloween' | 'navidad' | 'amor_amistad' | 'anio_nuevo' | 'dia_madre';

interface PropiedadesIconoTema {
    size?: number;
    strokeWidth?: number;
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
 * 9 posiciones fijas (compartidas por cualquier tema) donde dispersar
 * los íconos decorativos: distintos tamaños, rotaciones y ubicaciones
 * para que no se vean en fila ni repetitivos.
 */
export const POSICIONES_DECORATIVAS: ReadonlyArray<{ top: string; left: string; size: number; rotate: number }> = [
    { top: '10%', left: '5%', size: 18, rotate: -12 },
    { top: '70%', left: '8%', size: 24, rotate: 15 },
    { top: '20%', left: '85%', size: 20, rotate: 8 },
    { top: '55%', left: '92%', size: 16, rotate: -20 },
    { top: '85%', left: '78%', size: 22, rotate: 25 },
    { top: '5%', left: '45%', size: 14, rotate: -8 },
    { top: '40%', left: '60%', size: 18, rotate: 30 },
    { top: '90%', left: '35%', size: 16, rotate: -15 },
    { top: '15%', left: '65%', size: 20, rotate: 10 },
];

export function obtenerIconosTema(valor: unknown): ComponentType<PropiedadesIconoTema>[] | null {
    if (typeof valor !== 'string' || valor === 'ninguno' || !(valor in ICONOS_POR_TEMA)) {
        return null;
    }

    return ICONOS_POR_TEMA[valor as Exclude<TemaDecorativo, 'ninguno'>];
}
