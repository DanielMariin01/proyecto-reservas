import type { ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { eliminarUsuarioNegocio, obtenerUsuariosNegocio } from '../api/usuarios';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import { OPCIONES_ROL } from './opcionesRol';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    usuarioActualId: number;
}

function etiquetaRol(rol: string | null): string {
    return OPCIONES_ROL.find((opcion) => opcion.valor === rol)?.etiqueta ?? '—';
}

export default function PaginaUsuarios({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, usuarioActualId }: Propiedades): ReactElement {
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-usuarios', negocioSlug],
        queryFn: () => obtenerUsuariosNegocio(negocioSlug),
    });

    const mutacionEliminar = useMutation({
        mutationFn: (id: number) => eliminarUsuarioNegocio(negocioSlug, id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['panel-usuarios', negocioSlug] }),
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Usuarios</h2>
                    <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/usuarios/create`)}>Invitar usuario</Boton>
                </div>

                <TablaPaginada
                    columnas={[
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'email', etiqueta: 'Correo', render: (fila) => fila.email },
                        { clave: 'rol', etiqueta: 'Rol', render: (fila) => etiquetaRol(fila.rol) },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex gap-2">
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/app/${negocioSlug}/usuarios/${fila.id}/edit`)}>
                                        Editar
                                    </Boton>
                                    {fila.id !== usuarioActualId && (
                                        <Boton
                                            variante="texto"
                                            disabled={mutacionEliminar.isPending}
                                            onClick={() => {
                                                if (window.confirm(`¿Eliminar a "${fila.nombre}" de este negocio?`)) {
                                                    mutacionEliminar.mutate(fila.id);
                                                }
                                            }}
                                        >
                                            Eliminar
                                        </Boton>
                                    )}
                                </div>
                            ),
                        },
                    ]}
                    filas={consulta.data ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no hay usuarios en este negocio."
                    codigoError="REF-USR0"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={1}
                    totalPaginas={1}
                    onCambiarPagina={() => {}}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
