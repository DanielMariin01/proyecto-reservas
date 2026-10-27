<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cuenta no disponible</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <main class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-md rounded-tarjeta bg-blanco p-8 text-center shadow-tarjeta">
                <h1 class="mb-2 text-xl font-semibold text-tinta">
                    @if ($negocio->estado_suscripcion === \App\Enums\EstadoSuscripcion::Cancelado)
                        Este negocio fue cancelado
                    @else
                        Tu cuenta está suspendida
                    @endif
                </h1>
                <p class="text-sm text-texto-suave">
                    @if ($negocio->estado_suscripcion === \App\Enums\EstadoSuscripcion::Cancelado)
                        Comunícate con soporte si quieres reactivarlo.
                    @else
                        Comunícate con soporte para ponerte al día con tu suscripción.
                    @endif
                </p>
            </div>
        </main>
    </body>
</html>
