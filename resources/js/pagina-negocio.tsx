import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaNegocio from './PaginaNegocio';

const contenedor = document.getElementById('pagina-negocio');

if (contenedor) {
    const slug = contenedor.dataset.negocioSlug ?? '';
    const nombreNegocio = contenedor.dataset.negocioNombre ?? '';
    const direccion = contenedor.dataset.negocioDireccion ?? null;

    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaNegocio slug={slug} nombreNegocio={nombreNegocio} direccion={direccion} />
        </QueryClientProvider>,
    );
}
