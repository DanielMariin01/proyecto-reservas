<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Algo falló</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <main class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-md rounded-tarjeta bg-blanco p-8 text-center shadow-tarjeta">
                <h1 class="mb-2 text-xl font-semibold text-tinta">{{ $mensaje }}</h1>
                <p class="mb-4 text-sm text-texto-suave">Si el problema sigue, cuéntale a soporte y comparte este código.</p>
                <p class="mb-6 inline-block rounded-boton bg-fondo-panel px-3 py-1 text-sm font-semibold text-tinta">
                    {{ $codigoReferencia }}
                </p>
                <div>
                    <a href="{{ url()->previous('/') }}" class="inline-block rounded-boton bg-primario px-6 py-3 text-sm font-semibold text-blanco">
                        Reintentar
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
