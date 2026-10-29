import type { CSSProperties } from 'react';

const FONDO_POR_DEFECTO = '#FFFFFF';
const TEXTO_POR_DEFECTO = '#3E4043';

/**
 * Excepción documentada en la skill diseno-reservas (sección 1): estos 2
 * campos de bloque son color libre, no tokens — el super admin ve una
 * advertencia de contraste al elegirlos (EsquemaBloquesPagina), pero
 * nunca se bloquea, así que React los aplica tal cual vengan.
 */
export function obtenerEstiloColor(datos: Record<string, unknown>): CSSProperties {
    const colorFondo = typeof datos.colorFondo === 'string' && datos.colorFondo !== '' ? datos.colorFondo : FONDO_POR_DEFECTO;
    const colorTexto = typeof datos.colorTexto === 'string' && datos.colorTexto !== '' ? datos.colorTexto : TEXTO_POR_DEFECTO;

    return { backgroundColor: colorFondo, color: colorTexto };
}
