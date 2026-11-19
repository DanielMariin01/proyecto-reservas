import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import Alerta from '@/componentes/Alerta';
import { obtenerCliente } from '../api/clientes';
import { crearHistoriaClinica, type DatosHistoriaClinica } from '../api/historiasClinicas';
import { obtenerProfesionales } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioHistoriaClinica from './FormularioHistoriaClinica';

const ESPERA_ANTES_DE_VOLVER_MS = 1200;

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

    const [guardadoConExito, setGuardadoConExito] = useState(false);

    const mutacion = useMutation({
        mutationFn: (datos: DatosHistoriaClinica) => crearHistoriaClinica(negocioSlug, clienteId, datos),
        onSuccess: () => {
            setGuardadoConExito(true);
            setTimeout(() => {
                window.location.href = `/app/${negocioSlug}/clientes/${clienteId}`;
            }, ESPERA_ANTES_DE_VOLVER_MS);
        },
    });

    function volver(): void {
        window.location.href = `/app/${negocioSlug}/clientes/${clienteId}`;
    }

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Nuevo registro de historia clínica</h2>

                {guardadoConExito && <Alerta tipo="exito">Registro guardado correctamente.</Alerta>}

                {(consultaCliente.isLoading || consultaProfesionales.isLoading) && (
                    <div className="h-28 max-w-3xl animate-pulse rounded-tarjeta bg-gris-claro" />
                )}

                {consultaCliente.data && consultaProfesionales.data && (
                    <FormularioHistoriaClinica
                        cliente={consultaCliente.data}
                        profesionales={consultaProfesionales.data}
                        enviando={mutacion.isPending || guardadoConExito}
                        textoBoton="Guardar"
                        onGuardar={(datos) => mutacion.mutateAsync(datos)}
                        onCancelar={volver}
                    />
                )}
            </div>
        </LayoutPanelNegocio>
    );
}
