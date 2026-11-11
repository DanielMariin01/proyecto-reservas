<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar usuario</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-usuarios-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="panel-usuarios-editar"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('filament.app.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-usuario-id="{{ $usuario->id }}"
            data-usuario-actual-id="{{ auth()->id() }}"
            data-nombre="{{ $usuario->nombre }}"
            data-telefono="{{ $usuario->telefono }}"
            data-rol="{{ $rol }}"
        ></div>
    </body>
</html>
