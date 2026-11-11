import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaVerNegocio from './admin/paginas/PaginaVerNegocio';

const contenedor = document.getElementById('admin-negocios-ver');

if (contenedor) {
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const slug = contenedor.dataset.slug ?? '';
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaVerNegocio urlSalir={urlSalir} tokenCsrf={tokenCsrf} slug={slug} />
        </QueryClientProvider>,
    );
}
