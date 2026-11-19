<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ingresar</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <main class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-tarjeta bg-blanco p-8 shadow-tarjeta">
                <p id="enlace-volver" class="mb-4 hidden text-sm">
                    <a href="#" class="text-primario hover:underline">← Volver</a>
                </p>

                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-celeste text-primario">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 12h4" />
                        <path d="M10 8h4" />
                        <path d="M14 21v-3a2 2 0 0 0-4 0v3" />
                        <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2" />
                        <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16" />
                    </svg>
                </div>

                <h1 class="mb-6 text-center text-xl font-semibold text-tinta">Ingresar</h1>

                @if (session('status'))
                    <p class="mb-4 text-center text-sm text-tinta">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="mb-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-tinta">Correo</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="h-12 w-full rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-tinta">Contraseña</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="h-12 w-full rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-texto-suave">
                            <input type="checkbox" name="recordarme">
                            Recordarme
                        </label>
                        <a href="{{ route('password.request') }}" class="text-primario hover:underline">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button
                        type="submit"
                        class="h-11 w-full rounded-boton bg-primario font-semibold text-blanco hover:bg-acento"
                    >
                        Ingresar
                    </button>
                </form>
            </div>
        </main>

        <script>
            (function () {
                var referencia = document.referrer;

                if (!referencia || referencia.indexOf(window.location.origin + '/') !== 0 || referencia === window.location.href) {
                    return;
                }

                var contenedor = document.getElementById('enlace-volver');
                var enlace = contenedor.querySelector('a');
                enlace.href = referencia;
                contenedor.classList.remove('hidden');
            })();
        </script>
    </body>
</html>
