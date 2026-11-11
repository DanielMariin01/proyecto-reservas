import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarNegocio from './admin/paginas/PaginaEditarNegocio';

const contenedor = document.getElementById('admin-negocios-editar');

if (contenedor) {
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const slug = contenedor.dataset.slug ?? '';
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarNegocio urlSalir={urlSalir} tokenCsrf={tokenCsrf} slug={slug} />
        </QueryClientProvider>,
    );
}
