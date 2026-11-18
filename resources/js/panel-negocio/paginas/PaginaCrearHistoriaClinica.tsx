import type { ReactElement } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { obtenerCliente } from '../api/clientes';
import { crearHistoriaClinica, type DatosHistoriaClinica } from '../api/historiasClinicas';
import { obtenerProfesionales } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioHistoriaClinica from './FormularioHistoriaClinica';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    clienteId: number;
}

export default function PaginaCrearHistoriaClinica({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, clienteId }: Propiedades): ReactElement {
    const consultaCliente = useQuery({
        queryKey: ['panel-cliente', negocioSlug, clienteId],
        queryFn: () => obtenerCliente(negocioSlug, clienteId),
    });

    const consultaProfesionales = useQuery({
        queryKey: ['panel-profesionales', negocioSlug],
        queryFn: () => obtenerProfesionales(negocioSlug),
    });

    const mutacion = useMutation({
        mutationFn: (datos: DatosHistoriaClinica) => crearHistoriaClinica(negocioSlug, clienteId, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/clientes/${clienteId}`;
        },
    });

    function volver(): void {
        window.location.href = `/app/${negocioSlug}/clientes/${clienteId}`;
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo registro de historia clínica</h2>

                {(consultaCliente.isLoading || consultaProfesionales.isLoading) && (
                    <div className="h-28 max-w-3xl animate-pulse rounded-tarjeta bg-gris-claro" />
                )}

                {consultaCliente.data && consultaProfesionales.data && (
                    <FormularioHistoriaClinica
                        cliente={consultaCliente.data}
                        profesionales={consultaProfesionales.data}
                        enviando={mutacion.isPending}
                        textoBoton="Guardar"
                        onGuardar={(datos) => mutacion.mutateAsync(datos)}
                        onCancelar={volver}
                    />
                )}
            </div>
        </LayoutPanelNegocio>
    );
}
