import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';
import LayoutPanelNegocio from './LayoutPanelNegocio';

interface PropiedadesPanelNegocio {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PanelNegocio({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: PropiedadesPanelNegocio): ReactElement {
    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <Tarjeta>
                <p className="text-sm text-texto-suave">Bienvenido. Elige una sección del menú para empezar.</p>
            </Tarjeta>
        </LayoutPanelNegocio>
    );
}
