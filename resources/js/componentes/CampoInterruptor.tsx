import { Check } from 'lucide-react';
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
                className={`relative h-7 w-12 shrink-0 rounded-full transition-colors ${value ? 'bg-exito' : 'bg-gris-claro'}`}
            >
                <span
                    className={`absolute top-1 flex h-5 w-5 items-center justify-center rounded-full bg-blanco transition-transform ${value ? 'translate-x-6' : 'translate-x-1'}`}
                >
                    {value && <Check className="h-3 w-3 text-exito" strokeWidth={3} />}
                </span>
            </button>
        </div>
    );
}
