import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { crearCita, type DatosCrearCita, esErrorDeCrearCita } from '../api/citas';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioCita from './FormularioCita';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    puedeForzar: boolean;
}

export default function PaginaCrearCita({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, puedeForzar }: Propiedades): ReactElement {
    const [errorGeneral, setErrorGeneral] = useState<string | null>(null);

    const mutacion = useMutation({
        mutationFn: (datos: DatosCrearCita) => crearCita(negocioSlug, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/citas`;
        },
    });

    async function guardar(datos: DatosCrearCita): Promise<void> {
        setErrorGeneral(null);

        try {
            await mutacion.mutateAsync(datos);
        } catch (error) {
            if (esErrorDeCrearCita(error)) {
                setErrorGeneral(error.message);

                return;
            }

            throw error;
        }
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nueva cita</h2>
                <FormularioCita
                    slug={negocioSlug}
                    puedeForzar={puedeForzar}
                    enviando={mutacion.isPending}
                    errorGeneral={errorGeneral}
                    onGuardar={guardar}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
