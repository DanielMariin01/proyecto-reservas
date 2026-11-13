import type { ReactElement, ReactNode } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Calendar, CalendarCheck, ClipboardList, Clock, Globe, LayoutDashboard, Scissors, Star, Tags, UserCog, Users } from 'lucide-react';
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

    const enlaces: EnlaceMenuLateral[] = [
        { etiqueta: 'Panel del día', href: `/app/${negocioSlug}/panel-del-dia`, Icono: LayoutDashboard },
        { etiqueta: 'Agenda', href: `/app/${negocioSlug}/agenda`, Icono: Calendar },
        ...(esProfesional
            ? [{ etiqueta: 'Mis citas de hoy', href: `/app/${negocioSlug}/mis-citas-hoy`, Icono: ClipboardList }]
            : []),
        { etiqueta: 'Citas', href: `/app/${negocioSlug}/citas`, Icono: CalendarCheck },
        { etiqueta: 'Profesionales', href: `/app/${negocioSlug}/profesionales`, Icono: Users },
        { etiqueta: 'Servicios', href: `/app/${negocioSlug}/servicios`, Icono: Scissors },
        { etiqueta: 'Categorías de servicio', href: `/app/${negocioSlug}/categorias-servicio`, Icono: Tags },
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
