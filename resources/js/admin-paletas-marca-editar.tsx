import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarPaletaMarca from './admin/paginas/PaginaEditarPaletaMarca';

const contenedor = document.getElementById('admin-paletas-marca-editar');

if (contenedor) {
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const paletaId = Number(contenedor.dataset.paletaId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarPaletaMarca
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                paletaId={paletaId}
                valoresIniciales={{
                    nombre: contenedor.dataset.nombre ?? '',
                    color_primario: contenedor.dataset.colorPrimario ?? '',
                    color_secundario: contenedor.dataset.colorSecundario ?? '',
                }}
            />
        </QueryClientProvider>,
    );
}
