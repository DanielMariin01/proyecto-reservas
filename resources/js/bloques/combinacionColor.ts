export type CombinacionColor = 'clara' | 'oscura' | 'marca';

interface ClasesCombinacion {
    fondo: string;
    texto: string;
}

/**
 * Las únicas 3 combinaciones válidas (skill diseno-reservas, sección 1:
 * contraste AA). `bg-primario`/`text-primario` ya respetan la paleta de
 * marca del negocio porque son variables CSS que PaginaNegocio.tsx
 * sobrescribe en la raíz de la página pública.
 */
const COMBINACIONES: Record<CombinacionColor, ClasesCombinacion> = {
    clara: { fondo: 'bg-blanco', texto: 'text-tinta' },
    oscura: { fondo: 'bg-tinta', texto: 'text-blanco' },
    marca: { fondo: 'bg-primario', texto: 'text-blanco' },
};

export function obtenerCombinacionColor(valor: unknown): ClasesCombinacion {
    const clave = typeof valor === 'string' && valor in COMBINACIONES ? (valor as CombinacionColor) : 'clara';

    return COMBINACIONES[clave];
}
