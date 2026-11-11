<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Invitar usuario</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-usuarios-crear.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="panel-usuarios-crear"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('filament.app.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
        ></div>
    </body>
</html>
