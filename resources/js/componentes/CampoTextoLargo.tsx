import type { ReactElement, TextareaHTMLAttributes } from 'react';

interface PropiedadesCampoTextoLargo extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    etiqueta: string;
    error?: string;
}

export default function CampoTextoLargo({ etiqueta, error, id, className, ...resto }: PropiedadesCampoTextoLargo): ReactElement {
    const idCampo = id ?? etiqueta.toLowerCase().replace(/\s+/g, '-');

    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={idCampo} className="text-sm font-medium text-tinta">
                {etiqueta}
            </label>
            <textarea
                {...resto}
                id={idCampo}
                rows={resto.rows ?? 3}
                className={`rounded-boton border px-3 py-2 text-tinta focus:border-primario focus:outline-none ${
                    error ? 'border-red-500' : 'border-gris-claro'
                } ${className ?? ''}`}
            />
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
