import type { ReactElement, SelectHTMLAttributes } from 'react';

interface OpcionCampoSelect {
    valor: string;
    etiqueta: string;
}

interface PropiedadesCampoSelect extends Omit<SelectHTMLAttributes<HTMLSelectElement>, 'children'> {
    etiqueta: string;
    opciones: OpcionCampoSelect[];
    placeholder?: string;
    error?: string;
}

export default function CampoSelect({ etiqueta, opciones, placeholder, error, id, className, ...resto }: PropiedadesCampoSelect): ReactElement {
    const idCampo = id ?? etiqueta.toLowerCase().replace(/\s+/g, '-');

    return (
        <div className="flex flex-col gap-1">
            <label htmlFor={idCampo} className="text-sm font-medium text-tinta">
                {etiqueta}
            </label>
            <select
                {...resto}
                id={idCampo}
                className={`h-12 rounded-boton border px-3 text-tinta focus:border-primario focus:outline-none ${
                    error ? 'border-red-500' : 'border-gris-claro'
                } ${className ?? ''}`}
            >
                {placeholder && <option value="">{placeholder}</option>}
                {opciones.map((opcion) => (
                    <option key={opcion.valor} value={opcion.valor}>
                        {opcion.etiqueta}
                    </option>
                ))}
            </select>
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
