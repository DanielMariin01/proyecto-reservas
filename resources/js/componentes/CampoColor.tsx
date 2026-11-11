import type { ReactElement } from 'react';

interface PropiedadesCampoColor {
    etiqueta: string;
    value: string;
    onChange: (valor: string) => void;
    ayuda?: string;
    error?: string;
}

export default function CampoColor({ etiqueta, value, onChange, ayuda, error }: PropiedadesCampoColor): ReactElement {
    const idCampo = etiqueta.toLowerCase().replace(/\s+/g, '-');

    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={idCampo} className="text-sm font-medium text-tinta">
                {etiqueta}
            </label>
            <div className="flex items-center gap-3">
                <input
                    type="color"
                    value={value}
                    onChange={(evento) => onChange(evento.target.value)}
                    aria-label={`Selector de color para ${etiqueta}`}
                    className="h-12 w-12 cursor-pointer rounded-boton border border-gris-claro"
                />
                <input
                    id={idCampo}
                    type="text"
                    value={value}
                    onChange={(evento) => onChange(evento.target.value)}
                    aria-invalid={error ? 'true' : undefined}
                    aria-describedby={error ? `${idCampo}-error` : undefined}
                    className={`h-12 flex-1 rounded-boton border px-3 text-tinta focus:border-primario focus:outline-none ${
                        error ? 'border-red-500' : 'border-gris-claro'
                    }`}
                />
            </div>
            {ayuda && !error && <p className="text-sm text-texto-suave">{ayuda}</p>}
            {error && (
                <p id={`${idCampo}-error`} className="text-sm text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}
