import type { ReactElement } from 'react';

interface PropiedadesSelectorDeHora {
    texto: string;
    seleccionado: boolean;
    onElegir: () => void;
}

export default function SelectorDeHora({ texto, seleccionado, onElegir }: PropiedadesSelectorDeHora): ReactElement {
    return (
        <button
            type="button"
            onClick={onElegir}
            aria-pressed={seleccionado}
            className={`h-11 w-full rounded-boton border text-center font-semibold ${
                seleccionado ? 'border-primario bg-primario text-blanco' : 'border-gris-claro bg-blanco text-tinta'
            }`}
        >
            {texto}
        </button>
    );
}
