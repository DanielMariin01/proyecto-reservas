import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarCategoriaProducto from './panel-negocio/paginas/PaginaEditarCategoriaProducto';

const contenedor = document.getElementById('panel-categorias-producto-editar');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const categoriaId = Number(contenedor.dataset.categoriaId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarCategoriaProducto
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                categoriaId={categoriaId}
                valoresIniciales={{ nombre: contenedor.dataset.nombre ?? '' }}
            />
        </QueryClientProvider>,
    );
}
