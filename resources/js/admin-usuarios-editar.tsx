import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarUsuario from './admin/paginas/PaginaEditarUsuario';

const contenedor = document.getElementById('admin-usuarios-editar');

if (contenedor) {
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const usuarioId = Number(contenedor.dataset.usuarioId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarUsuario
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                usuarioId={usuarioId}
                valoresIniciales={{
                    nombre: contenedor.dataset.nombre ?? '',
                    email: contenedor.dataset.email ?? '',
                    telefono: contenedor.dataset.telefono ?? '',
                    es_super_admin: contenedor.dataset.esSuperAdmin === '1',
                }}
            />
        </QueryClientProvider>,
    );
}
