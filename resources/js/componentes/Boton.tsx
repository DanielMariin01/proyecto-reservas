import type { ButtonHTMLAttributes, ReactElement, ReactNode } from 'react';

type VarianteBoton = 'principal' | 'contorno' | 'texto';
type TamanoBoton = 'normal' | 'grande';

interface PropiedadesBoton extends ButtonHTMLAttributes<HTMLButtonElement> {
    variante?: VarianteBoton;
    tamano?: TamanoBoton;
    children: ReactNode;
}

const CLASES_POR_VARIANTE: Record<VarianteBoton, string> = {
    principal: 'bg-primario text-blanco hover:bg-acento',
    contorno: 'border border-gris-claro text-primario bg-blanco',
    texto: 'text-primario bg-transparent',
};

const CLASES_POR_TAMANO: Record<TamanoBoton, string> = {
    normal: 'h-11 px-4',
    grande: 'h-14 px-8 text-lg',
};

export default function Boton({ variante = 'principal', tamano = 'normal', className, children, disabled, ...resto }: PropiedadesBoton): ReactElement {
    const clasesVariante = CLASES_POR_VARIANTE[variante];
    const clasesTamano = CLASES_POR_TAMANO[tamano];

    return (
        <button
            {...resto}
            disabled={disabled}
            className={`rounded-boton font-semibold transition-opacity disabled:opacity-45 ${clasesTamano} ${clasesVariante} ${className ?? ''}`}
        >
            {children}
        </button>
    );
}
