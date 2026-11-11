import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaUsuarios from './panel-negocio/paginas/PaginaUsuarios';

const contenedor = document.getElementById('panel-usuarios');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const usuarioActualId = Number(contenedor.dataset.usuarioActualId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaUsuarios
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                usuarioActualId={usuarioActualId}
            />
        </QueryClientProvider>,
    );
}
