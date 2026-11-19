import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaTienda from './PaginaTienda';

const contenedor = document.getElementById('tienda');

if (contenedor) {
    const slug = contenedor.dataset.negocioSlug ?? '';

    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaTienda slug={slug} />
        </QueryClientProvider>,
    );
}
