import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { KeyRound } from 'lucide-react';
import Boton from '@/componentes/Boton';
import { eliminarUsuario, obtenerUsuarios, restablecerContrasenaUsuario } from '../api/usuarios';
import TablaPaginada from '../componentes/TablaPaginada';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaListaUsuarios({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['admin-usuarios', pagina],
        queryFn: () => obtenerUsuarios(pagina),
    });

    const mutacionEliminar = useMutation({
        mutationFn: eliminarUsuario,
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-usuarios'] }),
    });

    const mutacionRestablecer = useMutation({
        mutationFn: restablecerContrasenaUsuario,
        onSuccess: () => window.alert('Correo de restablecimiento enviado.'),
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Usuarios</h2>
                    <Boton onClick={() => (window.location.href = '/admin/usuarios/create')}>Nuevo usuario</Boton>
                </div>

                <TablaPaginada
                    columnas={[
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'email', etiqueta: 'Correo', render: (fila) => fila.email },
                        { clave: 'superAdmin', etiqueta: 'Super admin', render: (fila) => (fila.esSuperAdmin ? 'Sí' : 'No') },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex gap-2">
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/admin/usuarios/${fila.id}/edit`)}>
                                        Editar
                                    </Boton>
                                    <Boton
                                        variante="contorno"
                                        disabled={mutacionRestablecer.isPending}
                                        onClick={() => {
                                            if (window.confirm(`¿Enviar correo de restablecimiento de contraseña a ${fila.nombre}?`)) {
                                                mutacionRestablecer.mutate(fila.id);
                                            }
                                        }}
                                    >
                                        <KeyRound size={16} strokeWidth={1.75} />
                                    </Boton>
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminar.isPending}
                                        onClick={() => {
                                            if (window.confirm(`¿Eliminar al usuario "${fila.nombre}"?`)) {
                                                mutacionEliminar.mutate(fila.id);
                                            }
                                        }}
                                    >
                                        Eliminar
                                    </Boton>
                                </div>
                            ),
                        },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no hay usuarios."
                    codigoError="REF-USU1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutAdmin>
    );
}
