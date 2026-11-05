import type { ReactElement } from 'react';

interface PropiedadesAvatarIniciales {
    nombre: string;
    tamano?: number;
}

/**
 * Avatar genérico para cuando no hay foto: nunca se le pide una foto de
 * perfil al cliente, así que esto es lo único que se muestra junto a su
 * nombre en una reseña (y el respaldo de los testimonios manuales sin foto).
 */
export default function AvatarIniciales({ nombre, tamano = 40 }: PropiedadesAvatarIniciales): ReactElement {
    const iniciales = obtenerIniciales(nombre);

    return (
        <div
            className="flex shrink-0 items-center justify-center rounded-full bg-primario font-semibold text-blanco"
            style={{ width: tamano, height: tamano, fontSize: tamano * 0.4 }}
            aria-hidden="true"
        >
            {iniciales}
        </div>
    );
}

function obtenerIniciales(nombre: string): string {
    const palabras = nombre.trim().split(/\s+/).filter(Boolean);

    if (palabras.length === 0) {
        return '?';
    }

    const primera = palabras[0].charAt(0);
    const segunda = palabras.length > 1 ? palabras[palabras.length - 1].charAt(0) : '';

    return (primera + segunda).toUpperCase();
}
