import type { ReactElement } from 'react';
import Tarjeta from '@/componentes/Tarjeta';

export default function PantallaIngresar(): ReactElement {
    const huboErrorDeGoogle = new URLSearchParams(window.location.search).get('error') === 'google';

    return (
        <main className="flex min-h-screen items-center justify-center p-4">
            <Tarjeta className="w-full max-w-sm text-center">
                <h1 className="text-xl font-semibold text-tinta">Mi cuenta</h1>
                <p className="mt-2 text-sm text-texto-suave">
                    Entra con tu cuenta de Google para ver tus citas y tu historial en todos los negocios donde hayas reservado.
                </p>

                {huboErrorDeGoogle && (
                    <p className="mt-4 text-sm text-red-600">No pudimos conectar tu cuenta de Google. Intenta de nuevo.</p>
                )}

                <a
                    href="/mi-cuenta/google/redirect"
                    className="mt-6 flex h-11 w-full items-center justify-center rounded-boton bg-primario font-semibold text-blanco transition-opacity hover:bg-acento"
                >
                    Continuar con Google
                </a>
            </Tarjeta>
        </main>
    );
}
