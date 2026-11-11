<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar servicio</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-servicios-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="panel-servicios-editar"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('filament.app.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-servicio-id="{{ $servicio->id }}"
            data-nombre="{{ $servicio->nombre }}"
            data-categoria-servicio-id="{{ $servicio->categoria_servicio_id }}"
            data-descripcion="{{ $servicio->descripcion }}"
            data-foto="{{ $servicio->foto }}"
            data-duracion-minutos="{{ $servicio->duracion_minutos }}"
            data-precio="{{ $servicio->precio }}"
            data-margen-antes-minutos="{{ $servicio->margen_antes_minutos }}"
            data-margen-despues-minutos="{{ $servicio->margen_despues_minutos }}"
            data-intervalo-franjas-minutos="{{ $servicio->intervalo_franjas_minutos }}"
            data-visible="{{ $servicio->visible ? '1' : '0' }}"
        ></div>
    </body>
</html>
