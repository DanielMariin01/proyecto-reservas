const UMBRAL_CANAL_LINEAL = 0.03928;
const MINIMO_CONTRASTE_AA = 4.5;

function canalesRgb(colorHex: string): [number, number, number] {
    const hex = colorHex.replace('#', '');

    return [parseInt(hex.slice(0, 2), 16) / 255, parseInt(hex.slice(2, 4), 16) / 255, parseInt(hex.slice(4, 6), 16) / 255];
}

function linealizarCanal(canal: number): number {
    if (Number.isNaN(canal)) {
        return 0;
    }

    return canal <= UMBRAL_CANAL_LINEAL ? canal / 12.92 : ((canal + 0.055) / 1.055) ** 2.4;
}

function luminanciaRelativa(colorHex: string): number {
    const [rojo, verde, azul] = canalesRgb(colorHex);

    return 0.2126 * linealizarCanal(rojo) + 0.7152 * linealizarCanal(verde) + 0.0722 * linealizarCanal(azul);
}

function contrasteEntre(colorHexA: string, colorHexB: string): number {
    const masClara = Math.max(luminanciaRelativa(colorHexA), luminanciaRelativa(colorHexB));
    const masOscura = Math.min(luminanciaRelativa(colorHexA), luminanciaRelativa(colorHexB));

    return (masClara + 0.05) / (masOscura + 0.05);
}

/**
 * Advertencia no bloqueante de contraste para el color libre de bloque
 * (skill diseno-reservas, sección 1) — espejo en TS del mismo cálculo de
 * `App\Services\CalculadoraContraste`, solo para mostrar el aviso en el
 * Builder; nunca impide guardar.
 */
export function advertenciaContraste(colorFondo: string, colorTexto: string): string | null {
    if (!/^#[0-9a-fA-F]{6}$/.test(colorFondo) || !/^#[0-9a-fA-F]{6}$/.test(colorTexto)) {
        return null;
    }

    const contraste = contrasteEntre(colorFondo, colorTexto);

    if (contraste >= MINIMO_CONTRASTE_AA) {
        return null;
    }

    return `Este texto puede ser difícil de leer con este fondo (contraste ${contraste.toFixed(1)}:1; se recomienda 4,5:1 o más). Se puede guardar igual.`;
}
