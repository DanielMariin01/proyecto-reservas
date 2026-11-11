import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';
import LayoutAdmin from './LayoutAdmin';

interface PropiedadesPanelAdmin {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PanelAdmin({ urlSalir, tokenCsrf }: PropiedadesPanelAdmin): ReactElement {
    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <Tarjeta>
                <p className="text-sm text-texto-suave">Bienvenido. Elige una sección del menú para empezar.</p>
            </Tarjeta>
        </LayoutAdmin>
    );
}
