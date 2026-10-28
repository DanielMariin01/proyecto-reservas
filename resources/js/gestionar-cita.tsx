import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import GestionarCita from './gestionar-cita/GestionarCita';

const contenedor = document.getElementById('gestionar-cita');

if (contenedor) {
    const slug = contenedor.dataset.negocioSlug ?? '';
    const token = contenedor.dataset.token ?? '';

    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <GestionarCita slug={slug} token={token} />
        </QueryClientProvider>,
    );
}
