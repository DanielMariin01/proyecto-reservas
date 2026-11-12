<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Usuarios</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-usuarios.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        @include('partials.suplantacion-banner')
        <div
            id="panel-usuarios"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-usuario-actual-id="{{ auth()->id() }}"
        ></div>
    </body>
</html>
