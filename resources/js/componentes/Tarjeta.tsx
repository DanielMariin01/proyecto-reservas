import type { HTMLAttributes, ReactElement, ReactNode } from 'react';

interface PropiedadesTarjeta extends HTMLAttributes<HTMLDivElement> {
    children: ReactNode;
}

export default function Tarjeta({ className, children, ...resto }: PropiedadesTarjeta): ReactElement {
    return (
        <div {...resto} className={`rounded-tarjeta bg-blanco p-4 shadow-tarjeta sm:p-6 ${className ?? ''}`}>
            {children}
        </div>
    );
}
