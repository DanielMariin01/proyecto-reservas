import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Insignia from '@/componentes/Insignia';
import TablaPaginada from '@/componentes/TablaPaginada';
import { obtenerProfesionales } from '../api/profesionales';
import { type Cita, obtenerCitas } from '../api/citas';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import BotonesAccionesCita from './BotonesAccionesCita';
import ModalesAccionesCita from './ModalesAccionesCita';
import { useAccionesCita } from './useAccionesCita';

const ESTADOS = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'confirmada', label: 'Confirmada' },
    { value: 'en_atencion', label: 'En atención' },
    { value: 'completada', label: 'Completada' },
    { value: 'cancelada', label: 'Cancelada' },
    { value: 'no_asistio', label: 'No asistió' },
    { value: 'reprogramada', label: 'Reprogramada' },
];

const ESTADOS_CON_INSIGNIA = ['confirmada', 'pendiente', 'cancelada', 'no_asistio', 'completada', 'en_atencion'] as const;

function esEstadoConInsignia(estado: string): estado is (typeof ESTADOS_CON_INSIGNIA)[number] {
    return (ESTADOS_CON_INSIGNIA as readonly string[]).includes(estado);
}

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaCitas({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [profesionalId, setProfesionalId] = useState('');
    const [estado, setEstado] = useState('');
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-citas', negocioSlug, pagina, profesionalId, estado],
        queryFn: () => obtenerCitas(negocioSlug, { pagina, profesionalId, estado }),
    });

    const consultaProfesionales = useQuery({
        queryKey: ['panel-profesionales-para-filtro-citas', negocioSlug],
        queryFn: () => obtenerProfesionales(negocioSlug),
    });

    const estadoAcciones = useAccionesCita(negocioSlug, () => queryClient.invalidateQueries({ queryKey: ['panel-citas', negocioSlug] }));

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Citas</h2>
                    <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/citas/create`)}>Nueva cita</Boton>
                </div>

                <div className="flex flex-wrap items-end gap-4">
                    <div className="flex flex-col gap-1">
                        <label htmlFor="filtro-profesional" className="text-sm font-medium text-tinta">
                            Profesional
                        </label>
                        <select
                            id="filtro-profesional"
                            value={profesionalId}
                            onChange={(evento) => {
                                setPagina(1);
                                setProfesionalId(evento.target.value);
                            }}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Todos</option>
                            {(consultaProfesionales.data ?? []).map((profesional) => (
                                <option key={profesional.id} value={profesional.id}>
                                    {profesional.nombre}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="filtro-estado-cita" className="text-sm font-medium text-tinta">
                            Estado
                        </label>
                        <select
                            id="filtro-estado-cita"
                            value={estado}
                            onChange={(evento) => {
                                setPagina(1);
                                setEstado(evento.target.value);
                            }}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Todos</option>
                            {ESTADOS.map((opcion) => (
                                <option key={opcion.value} value={opcion.value}>
                                    {opcion.label}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <TablaPaginada<Cita>
                    columnas={[
                        { clave: 'inicio', etiqueta: 'Fecha y hora', render: (fila) => fila.inicioVisible },
                        { clave: 'profesional', etiqueta: 'Profesional', render: (fila) => fila.profesionalNombre ?? '—' },
                        { clave: 'servicio', etiqueta: 'Servicio', render: (fila) => fila.servicioNombre ?? '—' },
                        { clave: 'cliente', etiqueta: 'Cliente', render: (fila) => fila.clienteNombre ?? '—' },
                        {
                            clave: 'estado',
                            etiqueta: 'Estado',
                            render: (fila) => (esEstadoConInsignia(fila.estado) ? <Insignia estado={fila.estado} texto={fila.estadoEtiqueta} /> : fila.estadoEtiqueta),
                        },
                        { clave: 'origen', etiqueta: 'Origen', render: (fila) => fila.origenEtiqueta },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <BotonesAccionesCita acciones={fila.acciones} onAbrirAccion={(tipo) => estadoAcciones.abrirAccion(tipo, fila)} />
                            ),
                        },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="No hay citas con estos filtros."
                    codigoError="REF-CIT0"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>

            <ModalesAccionesCita {...estadoAcciones} />
        </LayoutPanelNegocio>
    );
}
