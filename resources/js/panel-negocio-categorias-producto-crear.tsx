import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaCrearCategoriaProducto from './panel-negocio/paginas/PaginaCrearCategoriaProducto';

const contenedor = document.getElementById('panel-categorias-producto-crear');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaCrearCategoriaProducto negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf} />
        </QueryClientProvider>,
    );
}
