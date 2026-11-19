import type { ComponentType, ReactElement, ReactNode } from 'react';
import { useEffect, useState } from 'react';
import { LogOut, Menu, X } from 'lucide-react';

export interface EnlaceMenuLateral {
    etiqueta: string;
    href: string;
    Icono: ComponentType<{ size?: number; strokeWidth?: number }>;
}

interface Propiedades {
    titulo: string;
    enlaces: EnlaceMenuLateral[];
    urlSalir: string;
    tokenCsrf: string;
    children: ReactNode;
}

export default function LayoutConMenuLateral({ titulo, enlaces, urlSalir, tokenCsrf, children }: Propiedades): ReactElement {
    const [menuAbierto, setMenuAbierto] = useState(false);

    useEffect(() => {
        if (!menuAbierto) {
            return;
        }

        function alPresionarTecla(evento: KeyboardEvent): void {
            if (evento.key === 'Escape') {
                setMenuAbierto(false);
            }
        }

        document.addEventListener('keydown', alPresionarTecla);

        return () => document.removeEventListener('keydown', alPresionarTecla);
    }, [menuAbierto]);

    return (
        <div className="flex min-h-screen flex-col md:flex-row">
            <header className="flex items-center justify-between bg-blanco p-4 shadow-tarjeta md:hidden">
                <h1 className="text-lg font-semibold text-tinta">{titulo}</h1>
                <button
                    type="button"
                    aria-label="Abrir menú"
                    onClick={() => setMenuAbierto(true)}
                    className="rounded-boton p-2 text-tinta hover:bg-fondo-panel"
                >
                    <Menu size={24} strokeWidth={1.75} />
                </button>
            </header>

            {menuAbierto && (
                <div
                    role="presentation"
                    onClick={() => setMenuAbierto(false)}
                    className="fixed inset-0 z-40 bg-tinta/40 md:hidden"
                />
            )}

            <aside
                role="dialog"
                aria-modal="true"
                aria-label="Menú"
                className={`fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col justify-between bg-blanco p-4 shadow-tarjeta transition-transform duration-200 ease-in-out md:static md:z-auto md:flex md:w-64 md:translate-x-0 ${
                    menuAbierto ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div>
                    <div className="mb-4 flex items-center justify-between px-2 md:mb-6">
                        <h1 className="text-lg font-semibold text-tinta">{titulo}</h1>
                        <button
                            type="button"
                            aria-label="Cerrar menú"
                            onClick={() => setMenuAbierto(false)}
                            className="rounded-boton p-1 text-texto-suave hover:bg-fondo-panel hover:text-tinta md:hidden"
                        >
                            <X size={20} strokeWidth={1.75} />
                        </button>
                    </div>

                    <nav className="flex flex-col space-y-1">
                        {enlaces.map(({ etiqueta, href, Icono }) => (
                            <a
                                key={href}
                                href={href}
                                onClick={() => setMenuAbierto(false)}
                                className="flex min-h-11 items-center gap-3 rounded-boton px-3 py-2 text-sm text-texto-suave hover:bg-fondo-panel hover:text-tinta"
                            >
                                <Icono size={18} strokeWidth={1.75} />
                                {etiqueta}
                            </a>
                        ))}
                    </nav>
                </div>

                <form method="POST" action={urlSalir} className="mt-4 md:mt-0">
                    <input type="hidden" name="_token" value={tokenCsrf} />
                    <button
                        type="submit"
                        className="flex min-h-11 w-full items-center gap-3 rounded-boton px-3 py-2 text-sm text-texto-suave hover:bg-fondo-panel hover:text-tinta"
                    >
                        <LogOut size={18} strokeWidth={1.75} />
                        Cerrar sesión
                    </button>
                </form>
            </aside>

            <main className="flex-1 p-4 md:p-8">{children}</main>
        </div>
    );
}
