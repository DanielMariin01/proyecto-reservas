<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Elige un negocio</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center gap-4 p-4">
            <h1 class="text-center text-xl font-semibold text-tinta">¿A cuál negocio quieres entrar?</h1>

            @foreach ($negocios as $negocio)
                <a
                    href="{{ route('elegir-negocio.entrar', $negocio) }}"
                    class="flex items-center justify-between rounded-tarjeta bg-blanco p-4 shadow-tarjeta hover:bg-celeste"
                >
                    <span class="font-medium text-tinta">{{ $negocio->nombre }}</span>
                    <span class="text-sm text-texto-suave">{{ $negocio->estado_suscripcion->etiqueta() }}</span>
                </a>
            @endforeach
        </main>
    </body>
</html>
