import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearUsuario, type DatosUsuario } from '../api/usuarios';
import FormularioUsuario from './FormularioUsuario';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearUsuario({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: crearUsuario,
        onSuccess: () => {
            window.location.href = '/admin/usuarios';
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo usuario</h2>
                <FormularioUsuario
                    enviando={mutacion.isPending}
                    textoBoton="Crear"
                    onGuardar={(datos: DatosUsuario) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutAdmin>
    );
}
