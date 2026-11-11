import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { type DatosInvitarUsuario, invitarUsuario } from '../api/usuarios';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioInvitarUsuario from './FormularioInvitarUsuario';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearUsuario({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosInvitarUsuario) => invitarUsuario(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/usuarios`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Invitar usuario</h2>
                <FormularioInvitarUsuario slug={negocioSlug} enviando={mutacion.isPending} onGuardar={(datos) => mutacion.mutateAsync(datos)} />
            </div>
        </LayoutPanelNegocio>
    );
}
