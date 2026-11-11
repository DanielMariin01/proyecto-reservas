import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarUsuarioNegocio, type DatosActualizarUsuario, eliminarUsuarioNegocio } from '../api/usuarios';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioEditarUsuarioNegocio from './FormularioEditarUsuarioNegocio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    usuarioId: number;
    valoresIniciales: DatosActualizarUsuario;
    usuarioActualId: number;
}

export default function PaginaEditarUsuario({
    negocioNombre,
    negocioSlug,
    urlSalir,
    tokenCsrf,
    usuarioId,
    valoresIniciales,
    usuarioActualId,
}: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosActualizarUsuario) => actualizarUsuarioNegocio(negocioSlug, usuarioId, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/usuarios`;
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarUsuarioNegocio(negocioSlug, usuarioId),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/usuarios`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar usuario</h2>
                    {usuarioId !== usuarioActualId && (
                        <Boton
                            variante="texto"
                            disabled={mutacionEliminar.isPending}
                            onClick={() => {
                                if (window.confirm('¿Eliminar este usuario del negocio?')) {
                                    mutacionEliminar.mutate();
                                }
                            }}
                        >
                            Eliminar
                        </Boton>
                    )}
                </div>
                <FormularioEditarUsuarioNegocio
                    slug={negocioSlug}
                    valoresIniciales={valoresIniciales}
                    enviando={mutacion.isPending}
                    onGuardar={(datos) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
