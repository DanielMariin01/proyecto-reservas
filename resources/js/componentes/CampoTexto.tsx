import type { InputHTMLAttributes, ReactElement } from 'react';

interface PropiedadesCampoTexto extends InputHTMLAttributes<HTMLInputElement> {
    etiqueta: string;
    error?: string;
}

export default function CampoTexto({ etiqueta, error, id, className, ...resto }: PropiedadesCampoTexto): ReactElement {
    const idCampo = id ?? etiqueta.toLowerCase().replace(/\s+/g, '-');

    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={idCampo} className="text-sm font-medium text-tinta">
                {etiqueta}
            </label>
            <input
                {...resto}
                id={idCampo}
                className={`h-12 rounded-boton border px-3 text-tinta focus:border-primario focus:outline-none ${
                    error ? 'border-red-500' : 'border-gris-claro'
                } ${className ?? ''}`}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={error ? `${idCampo}-error` : undefined}
            />
            {error && (
                <p id={`${idCampo}-error`} className="text-sm text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}
