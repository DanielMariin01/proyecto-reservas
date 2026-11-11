import type { ReactElement } from 'react';

interface PropiedadesCampoInterruptor {
    etiqueta: string;
    ayuda?: string;
    value: boolean;
    onChange: (valor: boolean) => void;
}

export default function CampoInterruptor({ etiqueta, ayuda, value, onChange }: PropiedadesCampoInterruptor): ReactElement {
    return (
        <div className="flex items-start justify-between gap-4">
            <div>
                <p className="text-sm font-medium text-tinta">{etiqueta}</p>
                {ayuda && <p className="text-sm text-texto-suave">{ayuda}</p>}
            </div>

            <button
                type="button"
                role="switch"
                aria-checked={value}
                aria-label={etiqueta}
                onClick={() => onChange(!value)}
                className={`relative h-7 w-12 shrink-0 rounded-boton transition-colors ${value ? 'bg-primario' : 'bg-gris-claro'}`}
            >
                <span
                    className={`absolute top-1 h-5 w-5 rounded-boton bg-blanco transition-transform ${value ? 'translate-x-6' : 'translate-x-1'}`}
                />
            </button>
        </div>
    );
}
