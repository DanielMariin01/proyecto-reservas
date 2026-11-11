import type { ReactElement } from 'react';
import {
    Calendar,
    CalendarCheck,
    ClipboardList,
    Clock,
    Globe,
    LayoutDashboard,
    LogOut,
    Scissors,
    Star,
    Tags,
    UserCog,
    Users,
} from 'lucide-react';
import Tarjeta from '@/componentes/Tarjeta';

interface EnlaceMenu {
    etiqueta: string;
    ruta: string;
    Icono: typeof Calendar;
}

const ENLACES_MENU: EnlaceMenu[] = [
    { etiqueta: 'Panel del día', ruta: 'panel-del-dia', Icono: LayoutDashboard },
    { etiqueta: 'Agenda', ruta: 'agenda', Icono: Calendar },
    { etiqueta: 'Mis citas de hoy', ruta: 'mis-citas-hoy', Icono: ClipboardList },
    { etiqueta: 'Citas', ruta: 'citas', Icono: CalendarCheck },
    { etiqueta: 'Profesionales', ruta: 'profesionales', Icono: Users },
    { etiqueta: 'Servicios', ruta: 'servicios', Icono: Scissors },
    { etiqueta: 'Categorías de servicio', ruta: 'categorias-servicio', Icono: Tags },
    { etiqueta: 'Mi horario', ruta: 'mi-horario', Icono: Clock },
    { etiqueta: 'Página web', ruta: 'pagina-web', Icono: Globe },
    { etiqueta: 'Reseñas', ruta: 'resenas', Icono: Star },
    { etiqueta: 'Usuarios', ruta: 'usuarios', Icono: UserCog },
];

interface PropiedadesPanelNegocio {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PanelNegocio({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: PropiedadesPanelNegocio): ReactElement {
    return (
        <div className="flex min-h-screen flex-col md:flex-row">
            <aside className="flex flex-col justify-between bg-blanco p-4 shadow-tarjeta md:w-64">
                <div>
                    <h1 className="mb-4 px-2 text-lg font-semibold text-tinta md:mb-6">{negocioNombre}</h1>

                    <nav className="flex flex-wrap gap-1 md:flex-col md:flex-nowrap md:space-y-1">
                        {ENLACES_MENU.map(({ etiqueta, ruta, Icono }) => (
                            <a
                                key={ruta}
                                href={`/app/${negocioSlug}/${ruta}`}
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

            <main className="flex-1 p-4 md:p-8">
                <Tarjeta>
                    <p className="text-sm text-texto-suave">
                        Bienvenido. Elige una sección del menú para empezar.
                    </p>
                </Tarjeta>
            </main>
        </div>
    );
}
