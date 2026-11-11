import type { ReactElement, ReactNode } from 'react';
import { Building2, History, LogOut, Palette, ServerCrash, ShieldAlert, Star, Users } from 'lucide-react';

interface EnlaceMenu {
    etiqueta: string;
    href: string;
    Icono: typeof Building2;
}

const ENLACES_MENU: EnlaceMenu[] = [
    { etiqueta: 'Negocios', href: '/admin/negocios', Icono: Building2 },
    { etiqueta: 'Usuarios', href: '/admin/usuarios', Icono: Users },
    { etiqueta: 'Paletas de marca', href: '/admin/paleta-marcas', Icono: Palette },
    { etiqueta: 'Reseñas', href: '/admin/resenas', Icono: Star },
    { etiqueta: 'Registro de auditoría', href: '/admin/registro-auditorias', Icono: History },
    { etiqueta: 'Registro de errores', href: '/admin/registro-errores', Icono: ShieldAlert },
    { etiqueta: 'Trabajos fallidos', href: '/admin/trabajos-fallidos', Icono: ServerCrash },
];

interface PropiedadesLayoutAdmin {
    urlSalir: string;
    tokenCsrf: string;
    children: ReactNode;
}

export default function LayoutAdmin({ urlSalir, tokenCsrf, children }: PropiedadesLayoutAdmin): ReactElement {
    return (
        <div className="flex min-h-screen flex-col md:flex-row">
            <aside className="flex flex-col justify-between bg-blanco p-4 shadow-tarjeta md:w-64">
                <div>
                    <h1 className="mb-4 px-2 text-lg font-semibold text-tinta md:mb-6">Panel admin</h1>

                    <nav className="flex flex-wrap gap-1 md:flex-col md:flex-nowrap md:space-y-1">
                        {ENLACES_MENU.map(({ etiqueta, href, Icono }) => (
                            <a
                                key={href}
                                href={href}
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
