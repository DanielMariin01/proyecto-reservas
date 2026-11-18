<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ficha del cliente</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-clientes-ver.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        @include('partials.suplantacion-banner')
        <div
            id="panel-clientes-ver"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-cliente-id="{{ $cliente->id }}"
        ></div>
    </body>
</html>
