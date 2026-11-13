import type { ReactElement, ReactNode } from 'react';
import { Building2, History, Palette, ServerCrash, ShieldAlert, Star, Users } from 'lucide-react';
import LayoutConMenuLateral, { type EnlaceMenuLateral } from '@/componentes/LayoutConMenuLateral';

const ENLACES_MENU: EnlaceMenuLateral[] = [
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
        <LayoutConMenuLateral titulo="Panel admin" enlaces={ENLACES_MENU} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            {children}
        </LayoutConMenuLateral>
    );
}
