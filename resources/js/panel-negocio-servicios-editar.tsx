import './bootstrap';
import { createRoot } from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import PaginaEditarServicio from './panel-negocio/paginas/PaginaEditarServicio';

const contenedor = document.getElementById('panel-servicios-editar');

if (contenedor) {
    const negocioNombre = contenedor.dataset.negocioNombre ?? '';
    const negocioSlug = contenedor.dataset.negocioSlug ?? '';
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';
    const servicioId = Number(contenedor.dataset.servicioId ?? '0');
    const clienteConsultas = new QueryClient();

    createRoot(contenedor).render(
        <QueryClientProvider client={clienteConsultas}>
            <PaginaEditarServicio
                negocioNombre={negocioNombre}
                negocioSlug={negocioSlug}
                urlSalir={urlSalir}
                tokenCsrf={tokenCsrf}
                servicioId={servicioId}
                valoresIniciales={{
                    nombre: contenedor.dataset.nombre ?? '',
                    categoria_servicio_id: contenedor.dataset.categoriaServicioId ? Number(contenedor.dataset.categoriaServicioId) : null,
                    descripcion: contenedor.dataset.descripcion ?? '',
                    foto: contenedor.dataset.foto || null,
                    duracion_minutos: Number(contenedor.dataset.duracionMinutos ?? '30'),
                    precio: Number(contenedor.dataset.precio ?? '0'),
                    margen_antes_minutos: contenedor.dataset.margenAntesMinutos ? Number(contenedor.dataset.margenAntesMinutos) : null,
                    margen_despues_minutos: contenedor.dataset.margenDespuesMinutos ? Number(contenedor.dataset.margenDespuesMinutos) : null,
                    intervalo_franjas_minutos: contenedor.dataset.intervaloFranjasMinutos
                        ? Number(contenedor.dataset.intervaloFranjasMinutos)
                        : null,
                    visible: contenedor.dataset.visible === '1',
                }}
            />
        </QueryClientProvider>,
    );
}
