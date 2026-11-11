import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/main.tsx',
                'resources/js/pagina-negocio.tsx',
                'resources/js/gestionar-cita.tsx',
                'resources/js/mi-cuenta.tsx',
                'resources/js/admin.tsx',
                'resources/js/admin-registro-auditorias.tsx',
                'resources/js/admin-registro-errores.tsx',
                'resources/js/admin-trabajos-fallidos.tsx',
                'resources/js/admin-paletas-marca.tsx',
                'resources/js/admin-paletas-marca-crear.tsx',
                'resources/js/admin-paletas-marca-editar.tsx',
                'resources/js/admin-usuarios.tsx',
                'resources/js/admin-usuarios-crear.tsx',
                'resources/js/admin-usuarios-editar.tsx',
                'resources/js/admin-resenas.tsx',
                'resources/js/admin-negocios.tsx',
                'resources/js/admin-negocios-crear.tsx',
                'resources/js/admin-negocios-editar.tsx',
                'resources/js/admin-negocios-ver.tsx',
                'resources/js/panel-negocio.tsx',
                'resources/js/panel-negocio-servicios.tsx',
                'resources/js/panel-negocio-servicios-crear.tsx',
                'resources/js/panel-negocio-servicios-editar.tsx',
                'resources/js/panel-negocio-categorias-servicio.tsx',
                'resources/js/panel-negocio-categorias-servicio-crear.tsx',
                'resources/js/panel-negocio-categorias-servicio-editar.tsx',
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/app/theme.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
