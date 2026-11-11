import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarServicio, type DatosServicio, eliminarServicio } from '../api/servicios';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioServicio from './FormularioServicio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    servicioId: number;
    valoresIniciales: DatosServicio;
}

export default function PaginaEditarServicio({
    negocioNombre,
    negocioSlug,
    urlSalir,
    tokenCsrf,
    servicioId,
    valoresIniciales,
}: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosServicio) => actualizarServicio(negocioSlug, servicioId, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/servicios`;
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarServicio(negocioSlug, servicioId),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/servicios`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar servicio</h2>
                    <Boton
                        variante="texto"
                        disabled={mutacionEliminar.isPending}
                        onClick={() => {
                            if (window.confirm('¿Eliminar este servicio?')) {
                                mutacionEliminar.mutate();
                            }
                        }}
                    >
                        Eliminar
                    </Boton>
                </div>
                <FormularioServicio
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
