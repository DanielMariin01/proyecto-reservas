import type { InputHTMLAttributes, ReactElement } from 'react';

interface PropiedadesCampoTelefono extends Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> {
    etiqueta: string;
    error?: string;
}

export default function CampoTelefono({ etiqueta, error, id, className, ...resto }: PropiedadesCampoTelefono): ReactElement {
    const idCampo = id ?? 'celular';

    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={idCampo} className="text-sm font-medium text-tinta">
                {etiqueta}
            </label>
            <div
                className={`flex h-12 items-center gap-2 rounded-boton border px-3 focus-within:border-primario ${
                    error ? 'border-red-500' : 'border-gris-claro'
                }`}
            >
                <span aria-hidden="true">🇨🇴</span>
                <span className="text-texto-suave">+57</span>
                <input
                    {...resto}
                    id={idCampo}
                    type="tel"
                    className={`h-full flex-1 border-0 text-tinta focus:outline-none ${className ?? ''}`}
                    aria-invalid={error ? 'true' : undefined}
                    aria-describedby={error ? `${idCampo}-error` : undefined}
                />
            </div>
            {error && (
                <p id={`${idCampo}-error`} className="text-sm text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}
