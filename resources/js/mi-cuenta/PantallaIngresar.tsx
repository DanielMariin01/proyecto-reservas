import type { ReactElement } from 'react';
import { UserCircle } from 'lucide-react';
import Tarjeta from '@/componentes/Tarjeta';

export default function PantallaIngresar(): ReactElement {
    const huboErrorDeGoogle = new URLSearchParams(window.location.search).get('error') === 'google';

    return (
        <main className="flex min-h-screen items-center justify-center p-4">
            <Tarjeta className="w-full max-w-sm">
                <div className="flex flex-col items-center text-center">
                    <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-celeste text-primario">
                        <UserCircle size={32} strokeWidth={1.75} />
                    </div>
                    <h1 className="text-xl font-semibold text-tinta">Mi cuenta</h1>
                    <p className="mt-2 text-sm text-texto-suave">
                        Entra con tu cuenta de Google para ver tus citas y tu historial en todos los negocios donde hayas reservado.
                    </p>
                </div>

                <div className="mt-6 border-t border-gris-claro pt-6">
                    <label className="mb-2 block text-left text-sm font-medium text-tinta">Accede con tu cuenta de Google</label>

                    {huboErrorDeGoogle && (
                        <p className="mb-3 text-sm text-red-600">No pudimos conectar tu cuenta de Google. Intenta de nuevo.</p>
                    )}

                    <a
                        href="/mi-cuenta/google/redirect"
                        className="flex h-11 w-full items-center justify-center rounded-boton bg-primario font-semibold text-blanco transition-opacity hover:bg-acento"
                    >
                        Continuar con Google
                    </a>
                </div>
            </Tarjeta>
        </main>
    );
}
