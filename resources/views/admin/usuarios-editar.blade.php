<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar usuario</title>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/admin-usuarios-editar.tsx'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <div
            id="admin-usuarios-editar"
            data-url-salir="{{ route('filament.admin.auth.logout') }}"
            data-token-csrf="{{ csrf_token() }}"
            data-usuario-id="{{ $usuario->id }}"
            data-nombre="{{ $usuario->nombre }}"
            data-email="{{ $usuario->email }}"
            data-telefono="{{ $usuario->telefono }}"
            data-es-super-admin="{{ $usuario->es_super_admin ? '1' : '0' }}"
        ></div>
    </body>
</html>
