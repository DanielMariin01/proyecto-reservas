<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Recuperar contraseña</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <main class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-tarjeta bg-blanco p-8 shadow-tarjeta">
                <h1 class="mb-2 text-center text-xl font-semibold text-tinta">Recuperar contraseña</h1>
                <p class="mb-6 text-center text-sm text-texto-suave">
                    Escribe tu correo y te enviamos un enlace para elegir una nueva contraseña.
                </p>

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

                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

                    <button
                        type="submit"
                        class="h-11 w-full rounded-boton bg-primario font-semibold text-blanco hover:bg-acento"
                    >
                        Enviar enlace
                    </button>
                </form>

                <p class="mt-4 text-center text-sm text-texto-suave">
                    <a href="{{ route('login') }}" class="text-primario hover:underline">Volver a ingresar</a>
                </p>
            </div>
        </main>
    </body>
</html>
