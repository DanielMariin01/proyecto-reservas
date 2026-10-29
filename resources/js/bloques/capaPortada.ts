import type { CSSProperties } from 'react';

export type CapaPortada = 'oscura' | 'clara';

interface EstiloCapa {
    capaEstilo: CSSProperties;
    texto: string;
    pildora: string;
}

/**
 * Degradado (no una capa pareja sobre toda la foto): opaco del lado
 * donde está el texto (izquierda) y se desvanece a transparente hacia
 * la derecha, para que se note la foto donde no hay texto encima. Las
 * opacidades pico (35 % oscura, 45 % clara) son las mismas ya
 * verificadas con fotos reales del proyecto — solo cambia que ahora
 * viven en el 0-40 % izquierdo en vez de cubrir el 100 % del ancho.
 */
const CAPAS: Record<CapaPortada, EstiloCapa> = {
    oscura: {
        capaEstilo: { backgroundImage: 'linear-gradient(to right, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.35) 40%, rgba(0,0,0,0) 75%)' },
        texto: 'text-blanco',
        pildora: 'bg-black/40',
    },
    clara: {
        capaEstilo: { backgroundImage: 'linear-gradient(to right, rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.45) 40%, rgba(255,255,255,0) 75%)' },
        texto: 'text-tinta',
        pildora: 'bg-blanco/70',
    },
};

export function obtenerCapaPortada(valor: unknown): EstiloCapa {
    const clave = typeof valor === 'string' && valor in CAPAS ? (valor as CapaPortada) : 'oscura';

    return CAPAS[clave];
}
