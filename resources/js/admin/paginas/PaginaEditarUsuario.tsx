import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarUsuario, type DatosUsuario, eliminarUsuario } from '../api/usuarios';
import FormularioUsuario from './FormularioUsuario';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
    usuarioId: number;
    valoresIniciales: DatosUsuario;
}

export default function PaginaEditarUsuario({ urlSalir, tokenCsrf, usuarioId, valoresIniciales }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosUsuario) => actualizarUsuario(usuarioId, datos),
        onSuccess: () => {
            window.location.href = '/admin/usuarios';
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarUsuario(usuarioId),
        onSuccess: () => {
            window.location.href = '/admin/usuarios';
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar usuario</h2>
                    <Boton
                        variante="texto"
                        disabled={mutacionEliminar.isPending}
                        onClick={() => {
                            if (window.confirm('¿Eliminar este usuario?')) {
                                mutacionEliminar.mutate();
                            }
                        }}
                    >
                        Eliminar
                    </Boton>
                </div>
                <FormularioUsuario
                    valoresIniciales={valoresIniciales}
                    enviando={mutacion.isPending}
                    textoBoton="Guardar cambios"
                    onGuardar={(datos: DatosUsuario) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutAdmin>
    );
}
