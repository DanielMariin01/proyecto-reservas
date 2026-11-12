import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaPanelDelDia from './panel-negocio/paginas/PaginaPanelDelDia';

const contenedor = document.getElementById('panel-panel-del-dia');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaPanelDelDia negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf} />
        </QueryClientProvider>,
    );
}
