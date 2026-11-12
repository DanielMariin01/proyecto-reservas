<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar paleta de marca</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-paletas-marca-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-paletas-marca-editar"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-paleta-id="{{ $paleta->id }}"
            data-nombre="{{ $paleta->nombre }}"
            data-color-primario="{{ $paleta->color_primario }}"
            data-color-secundario="{{ $paleta->color_secundario }}"
        ></div>
    </body>
</html>
