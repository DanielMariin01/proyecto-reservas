import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearNegocio, type DatosCrearNegocio, type DatosNegocio } from '../api/negocios';
import FormularioNegocio from './FormularioNegocio';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearNegocio({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: crearNegocio,
        onSuccess: () => {
            window.location.href = '/admin/negocios';
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo negocio</h2>
                <FormularioNegocio
                    esCreacion
                    enviando={mutacion.isPending}
                    textoBoton="Crear"
                    onGuardar={(datos: DatosNegocio | DatosCrearNegocio) => mutacion.mutateAsync(datos as DatosCrearNegocio)}
                />
            </div>
        </LayoutAdmin>
    );
}
