import type { ReactElement } from 'react';

interface PropiedadesCampoCasilla {
    etiqueta: string;
    checked: boolean;
    onChange: (valor: boolean) => void;
}

/**
 * Una casilla de selección múltiple (distinta de CampoInterruptor, que es
 * un interruptor sí/no): se usa en grupos donde se puede marcar más de una
 * a la vez, como las condiciones de salud o la valoración de piel/uña del
 * formulario de historia clínica.
 */
export default function CampoCasilla({ etiqueta, checked, onChange }: PropiedadesCampoCasilla): ReactElement {
    return (
        <label className="flex items-center gap-2 text-sm text-tinta">
            <input
                type="checkbox"
                checked={checked}
                onChange={(evento) => onChange(evento.target.checked)}
                className="h-5 w-5 rounded-boton border-gris-claro text-primario focus:ring-primario"
            />
            {etiqueta}
        </label>
    );
}
