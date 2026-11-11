import './bootstrap';
import { createRoot } from 'react-dom/client';
import PanelNegocio from './panel-negocio/PanelNegocio';

const contenedor = document.getElementById('panel-negocio');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';

    createRoot(contenedor).render(
        <PanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf} />,
    );
}
