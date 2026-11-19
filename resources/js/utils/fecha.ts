/**
 * CLAUDE.md: "Fechas guardadas en UTC y mostradas en la zona del
 * negocio" - nunca calcular "hoy" con `Date.toISOString()`, porque esa
 * conversión pasa a UTC. En Colombia (UTC-5), toISOString() ya muestra
 * la fecha de mañana desde las 7:00 p.m. hora local en adelante. Esta
 * función usa las partes LOCALES del Date (el navegador del staff está
 * en hora de Colombia, mismo supuesto que ya usa el resto del sistema),
 * nunca la representación UTC.
 */
export function formatearFechaISO(fecha: Date): string {
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');

    return `${anio}-${mes}-${dia}`;
}

export function hoyISO(): string {
    return formatearFechaISO(new Date());
}
