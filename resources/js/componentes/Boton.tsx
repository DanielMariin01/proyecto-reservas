import type { ButtonHTMLAttributes, ReactElement, ReactNode } from 'react';

type VarianteBoton = 'principal' | 'contorno' | 'texto';

interface PropiedadesBoton extends ButtonHTMLAttributes<HTMLButtonElement> {
    variante?: VarianteBoton;
    children: ReactNode;
}

const CLASES_POR_VARIANTE: Record<VarianteBoton, string> = {
    principal: 'bg-primario text-blanco hover:bg-acento',
    contorno: 'border border-gris-claro text-primario bg-blanco',
    texto: 'text-primario bg-transparent',
};

export default function Boton({ variante = 'principal', className, children, disabled, ...resto }: PropiedadesBoton): ReactElement {
    const clasesVariante = CLASES_POR_VARIANTE[variante];

    return (
        <button
            {...resto}
            disabled={disabled}
            className={`h-11 rounded-boton px-4 font-semibold transition-opacity disabled:opacity-45 ${clasesVariante} ${className ?? ''}`}
        >
            {children}
        </button>
    );
}
