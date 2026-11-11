import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarProfesional from './panel-negocio/paginas/PaginaEditarProfesional';

const contenedor = document.getElementById('panel-profesionales-editar');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarProfesional
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                profesional={{
                    id: Number(contenedor.dataset.profesionalId ?? '0'),
                    nombre: contenedor.dataset.nombre ?? '',
                    especialidad: contenedor.dataset.especialidad || null,
                    foto: contenedor.dataset.foto || null,
                    biografia: contenedor.dataset.biografia || null,
                    certificaciones: contenedor.dataset.certificaciones || null,
                    color: contenedor.dataset.color || null,
                    activo: contenedor.dataset.activo === '1',
                    usuarioEmail: contenedor.dataset.usuarioEmail || null,
                }}
            />
        </QueryClientProvider>,
    );
}
