/**
 * `foto`/`imagen` siempre guardan la ruta relativa del disco `public`
 * (ej. "servicios/corte.jpg"), nunca la URL completa (ver
 * App\Support\AlmacenamientoPublico). Esta función construye la URL de
 * visualización a partir de esa ruta, igual que Storage::url() en el
 * backend: mismo dominio, prefijo /storage.
 */
export function construirUrlAlmacenamiento(ruta: string): string {
    return `${window.location.origin}/storage/${ruta}`;
}
