import type { ReactElement, ReactNode } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
    Calendar,
    CalendarCheck,
    CalendarDays,
    ClipboardList,
    Clock,
    Contact,
    Globe,
    LayoutDashboard,
    PackageCheck,
    Scissors,
    ShoppingBag,
    Star,
    Tags,
    UserCog,
    Users,
    Wallet,
} from 'lucide-react';
import LayoutConMenuLateral, { type EnlaceMenuLateral } from '@/componentes/LayoutConMenuLateral';
import { obtenerUsuarioActual } from './api/usuarioActual';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    children: ReactNode;
}

export default function LayoutPanelNegocio({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, children }: Propiedades): ReactElement {
    const consultaUsuario = useQuery({
        queryKey: ['panel-usuario-actual', negocioSlug],
        queryFn: () => obtenerUsuarioActual(negocioSlug),
    });

    const esProfesional = consultaUsuario.data?.esProfesional ?? false;
    const puedeVerFinanzas = consultaUsuario.data?.puedeVerFinanzas ?? false;
    const puedeGestionarProductos = consultaUsuario.data?.puedeGestionarProductos ?? false;
    const puedeGestionarPedidos = consultaUsuario.data?.puedeGestionarPedidos ?? false;
    const puedeVerClientes = consultaUsuario.data?.puedeVerClientes ?? false;

    const enlaces: EnlaceMenuLateral[] = [
        { etiqueta: 'Panel del día', href: `/app/${negocioSlug}/panel-del-dia`, Icono: LayoutDashboard },
        ...(puedeVerFinanzas ? [{ etiqueta: 'Finanzas', href: `/app/${negocioSlug}/finanzas`, Icono: Wallet }] : []),
        { etiqueta: 'Agenda', href: `/app/${negocioSlug}/agenda`, Icono: Calendar },
        ...(esProfesional
            ? [
                  { etiqueta: 'Mis citas de hoy', href: `/app/${negocioSlug}/mis-citas-hoy`, Icono: ClipboardList },
                  { etiqueta: 'Mi agenda', href: `/app/${negocioSlug}/mi-agenda`, Icono: CalendarDays },
              ]
            : []),
        { etiqueta: 'Citas', href: `/app/${negocioSlug}/citas`, Icono: CalendarCheck },
        ...(puedeVerClientes ? [{ etiqueta: 'Clientes', href: `/app/${negocioSlug}/clientes`, Icono: Contact }] : []),
        { etiqueta: 'Profesionales', href: `/app/${negocioSlug}/profesionales`, Icono: Users },
        { etiqueta: 'Servicios', href: `/app/${negocioSlug}/servicios`, Icono: Scissors },
        { etiqueta: 'Categorías de servicio', href: `/app/${negocioSlug}/categorias-servicio`, Icono: Tags },
        ...(puedeGestionarProductos ? [{ etiqueta: 'Productos', href: `/app/${negocioSlug}/productos`, Icono: ShoppingBag }] : []),
        ...(puedeGestionarPedidos ? [{ etiqueta: 'Pedidos', href: `/app/${negocioSlug}/pedidos`, Icono: PackageCheck }] : []),
        ...(esProfesional ? [{ etiqueta: 'Mi horario', href: `/app/${negocioSlug}/mi-horario`, Icono: Clock }] : []),
        { etiqueta: 'Página web', href: `/app/${negocioSlug}/pagina-web`, Icono: Globe },
        { etiqueta: 'Reseñas', href: `/app/${negocioSlug}/resenas`, Icono: Star },
        { etiqueta: 'Usuarios', href: `/app/${negocioSlug}/usuarios`, Icono: UserCog },
    ];

    return (
        <LayoutConMenuLateral titulo={negocioNombre} enlaces={enlaces} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            {children}
        </LayoutConMenuLateral>
    );
}
