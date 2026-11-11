<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ver negocio</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-negocios-ver.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-negocios-ver"
            data-url-salir="{{ route('filament.admin.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-slug="{{ $negocio->slug }}"
        ></div>
    </body>
</html>
