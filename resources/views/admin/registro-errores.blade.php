<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registro de errores</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-registro-errores.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-registro-errores"
            data-url-salir="{{ route('salir') }}"
            data-token-csrf="{{ csrf_token() }}"
        ></div>
    </body>
</html>
