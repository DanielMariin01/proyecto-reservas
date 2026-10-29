import type { CSSProperties } from 'react';

const FONDO_POR_DEFECTO = '#FFFFFF';
const TEXTO_POR_DEFECTO = '#3E4043';
const OSCURECIDO_DEGRADADO = 0.15;

/**
 * Excepción documentada en la skill diseno-reservas (sección 1): estos 2
 * campos de bloque son color libre, no tokens — el super admin ve una
 * advertencia de contraste al elegirlos (EsquemaBloquesPagina), pero
 * nunca se bloquea, así que React los aplica tal cual vengan.
 */
export function obtenerColoresLibres(datos: Record<string, unknown>): { colorFondo: string; colorTexto: string } {
    const colorFondo = typeof datos.colorFondo === 'string' && datos.colorFondo !== '' ? datos.colorFondo : FONDO_POR_DEFECTO;
    const colorTexto = typeof datos.colorTexto === 'string' && datos.colorTexto !== '' ? datos.colorTexto : TEXTO_POR_DEFECTO;

    return { colorFondo, colorTexto };
}

export function obtenerEstiloColor(datos: Record<string, unknown>): CSSProperties {
    const { colorFondo, colorTexto } = obtenerColoresLibres(datos);

    return { backgroundColor: colorFondo, color: colorTexto };
}

/**
 * Degradado calculado a partir del mismo `colorFondo` que ya elige el
 * super admin (nunca un color nuevo ni suelto): oscurece ese mismo color
 * para darle profundidad a secciones muy llamativas (banner_oferta).
 */
export function obtenerEstiloGradiente(datos: Record<string, unknown>): CSSProperties {
    const { colorFondo, colorTexto } = obtenerColoresLibres(datos);

    return {
        backgroundImage: `linear-gradient(135deg, ${colorFondo}, ${oscurecerColor(colorFondo, OSCURECIDO_DEGRADADO)})`,
        color: colorTexto,
    };
}

function oscurecerColor(colorHex: string, porcentaje: number): string {
    const hex = colorHex.replace('#', '');

    if (hex.length !== 6) {
        return colorHex;
    }

    const factor = 1 - porcentaje;
    const canal = (inicio: number) => {
        const valor = Math.round(parseInt(hex.slice(inicio, inicio + 2), 16) * factor);

        return Math.max(0, Math.min(255, valor)).toString(16).padStart(2, '0');
    };

    return `#${canal(0)}${canal(2)}${canal(4)}`;
}
