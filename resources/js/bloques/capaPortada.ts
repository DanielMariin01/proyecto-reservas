export type CapaPortada = 'oscura' | 'clara';

interface ClasesCapa {
    capa: string;
    texto: string;
    pildora: string;
}

/**
 * Capa de legibilidad sobre la foto de fondo de `portada` — no es el
 * color libre de bloque (ver colorLibre.ts): son 2 opciones fijas para
 * que el texto se lea bien sobre cualquier foto, con suficiente
 * contraste a simple vista (sin el cálculo estricto de AA que sí se
 * exige a las paletas de marca).
 */
const CAPAS: Record<CapaPortada, ClasesCapa> = {
    oscura: { capa: 'bg-black/50', texto: 'text-blanco', pildora: 'bg-black/40' },
    clara: { capa: 'bg-blanco/70', texto: 'text-tinta', pildora: 'bg-blanco/70' },
};

export function obtenerCapaPortada(valor: unknown): ClasesCapa {
    const clave = typeof valor === 'string' && valor in CAPAS ? (valor as CapaPortada) : 'oscura';

    return CAPAS[clave];
}
