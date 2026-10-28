import type { LucideIcon } from 'lucide-react';
import type { ReactElement } from 'react';

interface PropiedadesLineaDetalle {
    icono: LucideIcon;
    texto: string;
}

export default function LineaDetalle({ icono: Icono, texto }: PropiedadesLineaDetalle): ReactElement {
    return (
        <div className="flex items-center gap-2 text-sm text-tinta">
            <Icono size={18} strokeWidth={1.75} className="text-texto-suave" />
            <span>{texto}</span>
        </div>
    );
}
