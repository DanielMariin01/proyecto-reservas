<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $negocio->nombre }}</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/pagina-negocio.tsx'])
    </head>
    <body>
        <div
            id="pagina-negocio"
            data-negocio-slug="{{ $negocio->slug }}"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-direccion="{{ $negocio->direccion }}"
        ></div>
    </body>
</html>
