import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarPaletaMarca, type DatosPaletaMarca, eliminarPaletaMarca } from '../api/paletasMarca';
import FormularioPaletaMarca from './FormularioPaletaMarca';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
    paletaId: number;
    valoresIniciales: DatosPaletaMarca;
}

export default function PaginaEditarPaletaMarca({ urlSalir, tokenCsrf, paletaId, valoresIniciales }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosPaletaMarca) => actualizarPaletaMarca(paletaId, datos),
        onSuccess: () => {
            window.location.href = '/admin/paleta-marcas';
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarPaletaMarca(paletaId),
        onSuccess: () => {
            window.location.href = '/admin/paleta-marcas';
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar paleta de marca</h2>
                    <Boton
                        variante="texto"
                        disabled={mutacionEliminar.isPending}
                        onClick={() => {
                            if (window.confirm('¿Eliminar esta paleta de marca?')) {
                                mutacionEliminar.mutate();
                            }
                        }}
                    >
                        Eliminar
                    </Boton>
                </div>
                <FormularioPaletaMarca
                    valoresIniciales={valoresIniciales}
                    enviando={mutacion.isPending}
                    textoBoton="Guardar cambios"
                    onGuardar={(datos: DatosPaletaMarca) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutAdmin>
    );
}
