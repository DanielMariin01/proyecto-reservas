<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $profesional->nombre }}</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/panel-negocio-profesionales-ver.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="panel-profesionales-ver"
            data-negocio-nombre="{{ $negocio->nombre }}"
            data-negocio-slug="{{ $negocio->slug }}"
            data-url-salir="{{ route('filament.app.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-profesional-id="{{ $profesional->id }}"
            data-nombre="{{ $profesional->nombre }}"
            data-especialidad="{{ $profesional->especialidad }}"
            data-foto="{{ $profesional->foto }}"
            data-biografia="{{ $profesional->biografia }}"
            data-certificaciones="{{ $profesional->certificaciones }}"
            data-color="{{ $profesional->color }}"
            data-activo="{{ $profesional->activo ? '1' : '0' }}"
            data-usuario-email="{{ $profesional->usuario?->email }}"
        ></div>
    </body>
</html>
