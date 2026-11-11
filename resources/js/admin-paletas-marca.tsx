import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaListaPaletasMarca from './admin/paginas/PaginaListaPaletasMarca';

const contenedor = document.getElementById('admin-paletas-marca');

if (contenedor) {
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaListaPaletasMarca urlSalir={urlSalir} tokenCsrf={tokenCsrf} />
        </QueryClientProvider>,
    );
}
