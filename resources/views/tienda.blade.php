<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tienda · {{ $negocio->nombre }}</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/tienda.tsx'])
    </head>
    <body>
        <div id="tienda" data-negocio-slug="{{ $negocio->slug }}"></div>
    </body>
</html>
