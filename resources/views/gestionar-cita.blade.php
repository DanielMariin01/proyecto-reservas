<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tu cita en {{ $negocio->nombre }}</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/gestionar-cita.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="gestionar-cita"
            data-negocio-slug="{{ $negocio->slug }}"
            data-token="{{ $token }}"
        ></div>
    </body>
</html>
