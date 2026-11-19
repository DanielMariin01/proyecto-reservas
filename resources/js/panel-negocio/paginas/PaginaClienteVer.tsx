import type { ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerCliente } from '../api/clientes';
import { eliminarHistoriaClinica, obtenerHistoriasClinicas, type HistoriaClinica } from '../api/historiasClinicas';
import { obtenerUsuarioActual } from '../api/usuarioActual';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import { OPCIONES_SERVICIO_DISENO } from './FormularioHistoriaClinica';

/** Mismo formato `d/m/Y` que el resto del panel (ver RecursoCita) — la fecha llega en ISO (yyyy-mm-dd) porque ese mismo valor se reutiliza como `value` del campo de fecha nativo al editar. */
function formatearFechaCorta(fechaIso: string): string {
    const [anio, mes, dia] = fechaIso.split('-');

    return `${dia}/${mes}/${anio}`;
}

function resumenServicioDiseno(historia: HistoriaClinica): string {
    if (historia.servicioDiseno === 'otro' && historia.servicioDisenoOtroTexto) {
        return historia.servicioDisenoOtroTexto;
    }

    return OPCIONES_SERVICIO_DISENO.find((opcion) => opcion.valor === historia.servicioDiseno)?.etiqueta ?? historia.servicioDiseno;
}

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    clienteId: number;
}

export default function PaginaClienteVer({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, clienteId }: Propiedades): ReactElement {
    const queryClient = useQueryClient();

    const consultaCliente = useQuery({
        queryKey: ['panel-cliente', negocioSlug, clienteId],
        queryFn: () => obtenerCliente(negocioSlug, clienteId),
    });

    const consultaUsuario = useQuery({
        queryKey: ['panel-usuario-actual', negocioSlug],
        queryFn: () => obtenerUsuarioActual(negocioSlug),
    });

    const puedeGestionarHistoriasClinicas = consultaUsuario.data?.puedeGestionarHistoriasClinicas ?? false;

    const consultaHistorias = useQuery({
        queryKey: ['panel-historias-clinicas', negocioSlug, clienteId],
        queryFn: () => obtenerHistoriasClinicas(negocioSlug, clienteId),
        enabled: puedeGestionarHistoriasClinicas,
    });

    const mutacionEliminar = useMutation({
        mutationFn: (id: number) => eliminarHistoriaClinica(negocioSlug, clienteId, id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['panel-historias-clinicas', negocioSlug, clienteId] }),
    });

    const cliente = consultaCliente.data;

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="max-w-3xl space-y-4">
                <Boton variante="texto" onClick={() => (window.location.href = `/app/${negocioSlug}/clientes`)}>
                    ← Volver a clientes
                </Boton>

                {consultaCliente.isLoading && <div className="h-28 animate-pulse rounded-tarjeta bg-gris-claro" />}

                {cliente && (
                    <Tarjeta className="space-y-1">
                        <h2 className="text-lg font-semibold text-tinta">{cliente.nombre}</h2>
                        <p className="text-sm text-texto-suave">Código: {cliente.codigoClienta}</p>
                        <p className="text-sm text-texto-suave">Celular: {cliente.celular}</p>
                        {cliente.documento && <p className="text-sm text-texto-suave">Documento: {cliente.documento}</p>}
                        {cliente.fechaNacimiento && <p className="text-sm text-texto-suave">Fecha de nacimiento: {cliente.fechaNacimiento}</p>}
                        {cliente.notas && <p className="text-sm text-texto-suave">Notas: {cliente.notas}</p>}
                    </Tarjeta>
                )}

                {puedeGestionarHistoriasClinicas && (
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-tinta">Historial clínico</h3>
                            <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/clientes/${clienteId}/historias-clinicas/create`)}>
                                Nuevo registro
                            </Boton>
                        </div>

                        {consultaHistorias.isLoading && <div className="h-20 animate-pulse rounded-tarjeta bg-gris-claro" />}

                        {consultaHistorias.isError && (
                            <Tarjeta>
                                <p className="text-sm text-texto-suave">Algo falló cargando el historial. Código REF-HCL1.</p>
                                <Boton variante="contorno" className="mt-4" onClick={() => consultaHistorias.refetch()}>
                                    Reintentar
                                </Boton>
                            </Tarjeta>
                        )}

                        {consultaHistorias.data && consultaHistorias.data.length === 0 && (
                            <p className="text-sm text-texto-suave">Todavía no hay registros de historia clínica.</p>
                        )}

                        {(consultaHistorias.data ?? []).map((historia) => (
                            <Tarjeta key={historia.id} className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p className="font-medium text-tinta">{formatearFechaCorta(historia.fecha)} · {resumenServicioDiseno(historia)}</p>
                                    <p className="text-sm text-texto-suave">Atendió: {historia.profesionalNombre ?? 'Sin asignar'}</p>
                                </div>
                                <div className="flex gap-2">
                                    <Boton
                                        variante="contorno"
                                        onClick={() =>
                                            (window.location.href = `/app/${negocioSlug}/clientes/${clienteId}/historias-clinicas/${historia.id}/edit`)
                                        }
                                    >
                                        Editar
                                    </Boton>
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminar.isPending}
                                        onClick={() => {
                                            if (window.confirm('¿Eliminar este registro de historia clínica?')) {
                                                mutacionEliminar.mutate(historia.id);
                                            }
                                        }}
                                    >
                                        Eliminar
                                    </Boton>
                                </div>
                            </Tarjeta>
                        ))}
                    </div>
                )}
            </div>
        </LayoutPanelNegocio>
    );
}
