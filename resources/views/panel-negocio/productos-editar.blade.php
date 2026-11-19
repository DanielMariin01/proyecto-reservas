<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar producto</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-productos-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        @include('partials.suplantacion-banner')
        <div
            id="panel-productos-editar"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-producto-id="{{ $producto->id }}"
            data-nombre="{{ $producto->nombre }}"
            data-categoria-producto-id="{{ $producto->categoria_producto_id }}"
            data-descripcion="{{ $producto->descripcion }}"
            data-foto="{{ $producto->foto }}"
            data-precio="{{ $producto->precio }}"
            data-visible="{{ $producto->visible ? '1' : '0' }}"
        ></div>
    </body>
</html>
