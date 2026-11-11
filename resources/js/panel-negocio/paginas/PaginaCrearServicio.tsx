import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearServicio, type DatosServicio } from '../api/servicios';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioServicio from './FormularioServicio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearServicio({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosServicio) => crearServicio(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/servicios`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo servicio</h2>
                <FormularioServicio slug={negocioSlug} enviando={mutacion.isPending} textoBoton="Crear" onGuardar={(datos) => mutacion.mutateAsync(datos)} />
            </div>
        </LayoutPanelNegocio>
    );
}
