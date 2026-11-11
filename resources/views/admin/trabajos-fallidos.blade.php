<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Trabajos fallidos</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-trabajos-fallidos.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-trabajos-fallidos"
            data-url-salir="{{ route('filament.admin.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
        ></div>
    </body>
</html>
