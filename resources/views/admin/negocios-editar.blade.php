<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar negocio</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-negocios-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-negocios-editar"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-slug="{{ $negocio->slug }}"
        ></div>
    </body>
</html>
