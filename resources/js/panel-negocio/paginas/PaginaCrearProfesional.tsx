import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearProfesional, type DatosCrearProfesional, type DatosProfesional } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioProfesional from './FormularioProfesional';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCrearProfesional({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosCrearProfesional) => crearProfesional(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/profesionales`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo profesional</h2>
                <FormularioProfesional
                    slug={negocioSlug}
                    esCreacion
                    enviando={mutacion.isPending}
                    textoBoton="Crear"
                    onGuardar={(datos: DatosProfesional | DatosCrearProfesional) => mutacion.mutateAsync(datos as DatosCrearProfesional)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
