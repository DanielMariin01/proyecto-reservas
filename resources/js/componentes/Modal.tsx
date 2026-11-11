import type { ReactElement, ReactNode } from 'react';
import { X } from 'lucide-react';

interface PropiedadesModal {
    titulo: string;
    children: ReactNode;
    onCerrar: () => void;
}

export default function Modal({ titulo, children, onCerrar }: PropiedadesModal): ReactElement {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/40 p-4">
            <div className="w-full max-w-lg rounded-tarjeta bg-blanco p-6 shadow-tarjeta">
                <div className="mb-4 flex items-center justify-between gap-4">
                    <h3 className="text-lg font-semibold text-tinta">{titulo}</h3>
                    <button type="button" aria-label="Cerrar" onClick={onCerrar} className="text-texto-suave hover:text-tinta">
                        <X size={20} strokeWidth={1.75} />
                    </button>
                </div>
                {children}
            </div>
        </div>
    );
}
