import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarHistoriaClinica from './panel-negocio/paginas/PaginaEditarHistoriaClinica';

const contenedor = document.getElementById('panel-historias-clinicas-editar');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const clienteId = Number(contenedor.dataset.clienteId ?? 0);
    const historiaClinicaId = Number(contenedor.dataset.historiaClinicaId ?? 0);
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarHistoriaClinica
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                clienteId={clienteId}
                historiaClinicaId={historiaClinicaId}
            />
        </QueryClientProvider>,
    );
}
