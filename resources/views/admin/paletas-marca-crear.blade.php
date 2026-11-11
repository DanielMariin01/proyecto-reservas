<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Nueva paleta de marca</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-paletas-marca-crear.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-paletas-marca-crear"
            data-url-salir="{{ route('filament.admin.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
        ></div>
    </body>
</html>
