export function leerTexto(datos: Record<string, unknown>, campo: string, porDefecto = ''): string {
    return typeof datos[campo] === 'string' ? (datos[campo] as string) : porDefecto;
}

export function leerBool(datos: Record<string, unknown>, campo: string, porDefecto = false): boolean {
    return typeof datos[campo] === 'boolean' ? (datos[campo] as boolean) : porDefecto;
}

export function leerArray<T>(datos: Record<string, unknown>, campo: string): T[] {
    return Array.isArray(datos[campo]) ? (datos[campo] as T[]) : [];
}

export function leerNumero(datos: Record<string, unknown>, campo: string, porDefecto: number): number {
    const valor = Number(datos[campo]);

    return datos[campo] !== undefined && Number.isFinite(valor) ? valor : porDefecto;
}

export function leerImagen(datos: Record<string, unknown>, campo: string): string | null {
    return typeof datos[campo] === 'string' ? (datos[campo] as string) : null;
}
