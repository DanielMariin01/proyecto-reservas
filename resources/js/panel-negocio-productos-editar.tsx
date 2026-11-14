import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarProducto from './panel-negocio/paginas/PaginaEditarProducto';

const contenedor = document.getElementById('panel-productos-editar');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const productoId = Number(contenedor.dataset.productoId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarProducto
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                productoId={productoId}
                valoresIniciales={{
                    nombre: contenedor.dataset.nombre ?? '',
                    descripcion: contenedor.dataset.descripcion ?? '',
                    foto: contenedor.dataset.foto || null,
                    precio: Number(contenedor.dataset.precio ?? '0'),
                    visible: contenedor.dataset.visible === '1',
                }}
            />
        </QueryClientProvider>,
    );
}
