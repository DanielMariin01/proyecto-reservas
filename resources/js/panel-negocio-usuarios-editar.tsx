import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarUsuario from './panel-negocio/paginas/PaginaEditarUsuario';

const contenedor = document.getElementById('panel-usuarios-editar');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const usuarioId = Number(contenedor.dataset.usuarioId ?? '0');
    const usuarioActualId = Number(contenedor.dataset.usuarioActualId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarUsuario
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                usuarioId={usuarioId}
                usuarioActualId={usuarioActualId}
                valoresIniciales={{
                    nombre: contenedor.dataset.nombre ?? '',
                    telefono: contenedor.dataset.telefono ?? '',
                    rol: contenedor.dataset.rol ?? 'dueno',
                }}
            />
        </QueryClientProvider>,
    );
}
