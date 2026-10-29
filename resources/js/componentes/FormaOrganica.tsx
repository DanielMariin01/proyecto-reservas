import type { ReactElement, ReactNode } from 'react';

interface PropiedadesFormaOrganica {
    id: string;
    children: ReactNode;
    className?: string;
}

/**
 * Recorta su contenido (una imagen) con una mancha asimétrica, no un
 * círculo ni un rectángulo. `clipPathUnits="objectBoundingBox"` hace que
 * el path (en coordenadas 0-1) escale solo con el tamaño real del
 * elemento, así se ve igual de bien en escritorio y en celular sin
 * recalcular nada.
 */
export default function FormaOrganica({ id, children, className }: PropiedadesFormaOrganica): ReactElement {
    return (
        <div className={`relative ${className ?? ''}`} style={{ clipPath: `url(#${id})` }}>
            <svg width="0" height="0" className="absolute">
                <defs>
                    <clipPath id={id} clipPathUnits="objectBoundingBox">
                        <path d="M0.52,0.06 C0.65,0.05 0.78,0.14 0.85,0.26 C0.93,0.38 0.94,0.54 0.89,0.67 C0.84,0.8 0.73,0.9 0.6,0.94 C0.47,0.98 0.32,0.96 0.21,0.88 C0.1,0.8 0.03,0.66 0.04,0.52 C0.05,0.38 0.13,0.25 0.24,0.17 C0.35,0.09 0.39,0.07 0.52,0.06 Z" />
                    </clipPath>
                </defs>
            </svg>
            {children}
        </div>
    );
}
