import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import AreaCliente from './mi-cuenta/AreaCliente';

const contenedor = document.getElementById('mi-cuenta');

if (contenedor) {
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <AreaCliente />
        </QueryClientProvider>,
    );
}
