import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarProducto, type DatosProducto, eliminarProducto } from '../api/productos';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioProducto from './FormularioProducto';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    productoId: number;
    valoresIniciales: DatosProducto;
}

export default function PaginaEditarProducto({
    negocioNombre,
    negocioSlug,
    urlSalir,
    tokenCsrf,
    productoId,
    valoresIniciales,
}: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosProducto) => actualizarProducto(negocioSlug, productoId, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/productos`;
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarProducto(negocioSlug, productoId),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/productos`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar producto</h2>
                    <Boton
                        variante="texto"
                        disabled={mutacionEliminar.isPending}
                        onClick={() => {
                            if (window.confirm('¿Eliminar este producto?')) {
                                mutacionEliminar.mutate();
                            }
                        }}
                    >
                        Eliminar
                    </Boton>
                </div>
                <FormularioProducto
                    slug={negocioSlug}
                    valoresIniciales={valoresIniciales}
                    enviando={mutacion.isPending}
                    textoBoton="Guardar cambios"
                    onGuardar={(datos) => mutacion.mutateAsync(datos)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
