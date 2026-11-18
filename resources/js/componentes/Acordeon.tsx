import { ChevronDown } from 'lucide-react';
import type { ReactElement, ReactNode } from 'react';

interface PropiedadesAcordeon {
    titulo: string;
    abierta: boolean;
    onCambiar: () => void;
    children: ReactNode;
}

/**
 * Una sección colapsable del formulario de historia clínica (sección 19 de
 * requerimientos.md): el padre controla cuál sección está abierta para que
 * solo una se despliegue a la vez, como en el formulario físico original.
 */
export default function Acordeon({ titulo, abierta, onCambiar, children }: PropiedadesAcordeon): ReactElement {
    return (
        <div className="overflow-hidden rounded-tarjeta bg-blanco shadow-tarjeta">
            <button
                type="button"
                onClick={onCambiar}
                aria-expanded={abierta}
                className="flex h-14 w-full items-center justify-between px-6 text-left"
            >
                <span className="font-semibold text-tinta">{titulo}</span>
                <ChevronDown
                    size={20}
                    strokeWidth={1.75}
                    className={`shrink-0 text-texto-suave transition-transform ${abierta ? 'rotate-180' : ''}`}
                />
            </button>

            {abierta && <div className="space-y-4 border-t border-gris-claro px-6 py-6">{children}</div>}
        </div>
    );
}
