import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { obtenerOpcionesNegocio } from '../api/registroErrores';
import { mostrarResena, obtenerResenas, ocultarResena } from '../api/resenas';
import TablaPaginada from '../componentes/TablaPaginada';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaResenas({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [negocioId, setNegocioId] = useState<number | null>(null);
    const [estado, setEstado] = useState('');
    const queryClient = useQueryClient();

    const opcionesNegocio = useQuery({ queryKey: ['admin-negocios-opciones'], queryFn: obtenerOpcionesNegocio });

    const consulta = useQuery({
        queryKey: ['admin-resenas', pagina, negocioId, estado],
        queryFn: () => obtenerResenas({ pagina, negocioId, estado }),
    });

    const mutacionOcultar = useMutation({
        mutationFn: ocultarResena,
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-resenas'] }),
    });

    const mutacionMostrar = useMutation({
        mutationFn: mostrarResena,
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-resenas'] }),
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Reseñas</h2>

                <div className="flex flex-wrap items-end gap-4">
                    <div className="flex flex-col gap-1">
                        <label htmlFor="filtro-negocio" className="text-sm font-medium text-tinta">
                            Negocio
                        </label>
                        <select
                            id="filtro-negocio"
                            value={negocioId ?? ''}
                            onChange={(evento) => {
                                setPagina(1);
                                setNegocioId(evento.target.value ? Number(evento.target.value) : null);
                            }}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Todos</option>
                            {(opcionesNegocio.data ?? []).map((negocio) => (
                                <option key={negocio.id} value={negocio.id}>
                                    {negocio.nombre}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="flex flex-col gap-1">
                        <label htmlFor="filtro-estado" className="text-sm font-medium text-tinta">
                            Estado
                        </label>
                        <select
                            id="filtro-estado"
                            value={estado}
                            onChange={(evento) => {
                                setPagina(1);
                                setEstado(evento.target.value);
                            }}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Todas</option>
                            <option value="publicada">Publicada</option>
                            <option value="oculta">Oculta</option>
                        </select>
                    </div>
                </div>

                <TablaPaginada
                    columnas={[
                        { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.fecha },
                        { clave: 'negocio', etiqueta: 'Negocio', render: (fila) => fila.negocio ?? '—' },
                        { clave: 'cliente', etiqueta: 'Cliente', render: (fila) => fila.cliente ?? '—' },
                        { clave: 'profesional', etiqueta: 'Profesional', render: (fila) => fila.profesional ?? '—' },
                        { clave: 'calificacion', etiqueta: 'Estrellas', render: (fila) => `${fila.calificacion} ★` },
                        { clave: 'comentario', etiqueta: 'Comentario', render: (fila) => fila.comentario },
                        { clave: 'estado', etiqueta: 'Estado', render: (fila) => fila.estadoEtiqueta },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) =>
                                fila.estaPublicada ? (
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionOcultar.isPending}
                                        onClick={() => {
                                            if (window.confirm('¿Ocultar esta reseña?')) {
                                                mutacionOcultar.mutate(fila.id);
                                            }
                                        }}
                                    >
                                        Ocultar
                                    </Boton>
                                ) : (
                                    <Boton variante="contorno" disabled={mutacionMostrar.isPending} onClick={() => mutacionMostrar.mutate(fila.id)}>
                                        Volver a publicar
                                    </Boton>
                                ),
                        },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="No hay reseñas con estos filtros."
                    codigoError="REF-RES1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutAdmin>
    );
}
