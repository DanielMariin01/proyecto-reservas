import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { obtenerSesion } from './api/clienteCuenta';
import PanelCitas from './PanelCitas';
import PantallaIngresar from './PantallaIngresar';

const CLAVE_SESION = ['mi-cuenta-sesion'];

export default function AreaCliente(): ReactElement {
    const sesionQuery = useQuery({ queryKey: CLAVE_SESION, queryFn: obtenerSesion });

    if (sesionQuery.isLoading) {
        return (
            <main className="flex min-h-screen items-center justify-center p-4">
                <div className="h-48 w-full max-w-sm animate-pulse rounded-tarjeta bg-gris-claro" />
            </main>
        );
    }

    if (sesionQuery.isError) {
        return (
            <main className="flex min-h-screen flex-col items-center justify-center gap-4 p-4 text-center">
                <p className="text-sm text-texto-suave">Algo falló cargando tu cuenta. Código REF-CTA1.</p>
                <Boton onClick={() => sesionQuery.refetch()}>Reintentar</Boton>
            </main>
        );
    }

    if (!sesionQuery.data) {
        return <PantallaIngresar />;
    }

    return <PanelCitas cuenta={sesionQuery.data} onSalir={() => sesionQuery.refetch()} />;
}
