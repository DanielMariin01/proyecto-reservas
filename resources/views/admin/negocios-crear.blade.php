<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Nuevo negocio</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-negocios-crear.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-negocios-crear"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
        ></div>
    </body>
</html>
