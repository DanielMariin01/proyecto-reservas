<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar categoría de servicio</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-categorias-servicio-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="panel-categorias-servicio-editar"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('filament.app.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-categoria-id="{{ $categoriaServicio->id }}"
            data-nombre="{{ $categoriaServicio->nombre }}"
        ></div>
    </body>
</html>
