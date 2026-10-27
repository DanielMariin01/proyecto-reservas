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
                <h1 class="mb-6 text-center text-xl font-semibold text-tinta">Ingresar</h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-boton bg-celeste p-3 text-sm text-tinta">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
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

                    <label class="flex items-center gap-2 text-sm text-texto-suave">
                        <input type="checkbox" name="recordarme">
                        Recordarme
                    </label>

                    <button
                        type="submit"
                        class="h-11 w-full rounded-boton bg-primario font-semibold text-blanco hover:bg-acento"
                    >
                        Ingresar
                    </button>
                </form>
            </div>
        </main>
    </body>
</html>
