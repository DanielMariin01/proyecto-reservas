import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { mostrarResena, obtenerResenas, ocultarResena, type ResenaNegocio, responderResena } from '../api/resenas';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalResponderResena from './ModalResponderResena';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaResenas({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [estado, setEstado] = useState('');
    const [calificacion, setCalificacion] = useState('');
    const [resenaAResponder, setResenaAResponder] = useState<ResenaNegocio | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-resenas', negocioSlug, pagina, estado, calificacion],
        queryFn: () => obtenerResenas(negocioSlug, { pagina, estado, calificacion }),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-resenas', negocioSlug] });
    }

    const mutacionResponder = useMutation({
        mutationFn: (respuesta: string) => responderResena(negocioSlug, resenaAResponder!.id, respuesta),
        onSuccess: () => {
            setResenaAResponder(null);
            invalidar();
        },
    });

    const mutacionOcultar = useMutation({
        mutationFn: (id: number) => ocultarResena(negocioSlug, id),
        onSuccess: invalidar,
    });

    const mutacionMostrar = useMutation({
        mutationFn: (id: number) => mostrarResena(negocioSlug, id),
        onSuccess: invalidar,
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Reseñas</h2>

                <div className="flex flex-wrap items-end gap-4">
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

                    <div className="flex flex-col gap-1">
                        <label htmlFor="filtro-calificacion" className="text-sm font-medium text-tinta">
                            Estrellas
                        </label>
                        <select
                            id="filtro-calificacion"
                            value={calificacion}
                            onChange={(evento) => {
                                setPagina(1);
                                setCalificacion(evento.target.value);
                            }}
                            className="h-12 rounded-boton border border-gris-claro px-3 text-tinta focus:border-primario focus:outline-none"
                        >
                            <option value="">Todas</option>
                            {[1, 2, 3, 4, 5].map((numero) => (
                                <option key={numero} value={numero}>
                                    {numero}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <TablaPaginada
                    columnas={[
                        { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.fecha },
                        { clave: 'cliente', etiqueta: 'Cliente', render: (fila) => fila.cliente ?? '—' },
                        { clave: 'profesional', etiqueta: 'Profesional', render: (fila) => fila.profesional ?? '—' },
                        { clave: 'calificacion', etiqueta: 'Estrellas', render: (fila) => `${fila.calificacion} ★` },
                        { clave: 'comentario', etiqueta: 'Comentario', render: (fila) => fila.comentario },
                        { clave: 'estado', etiqueta: 'Estado', render: (fila) => fila.estadoEtiqueta },
                        { clave: 'respuesta', etiqueta: 'Respuesta', render: (fila) => fila.respuestaTexto ?? 'Sin responder' },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex flex-wrap gap-2">
                                    <Boton variante="contorno" onClick={() => setResenaAResponder(fila)}>
                                        {fila.respuestaTexto === null ? 'Responder' : 'Editar respuesta'}
                                    </Boton>
                                    {fila.estaPublicada ? (
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
                                        <Boton variante="texto" disabled={mutacionMostrar.isPending} onClick={() => mutacionMostrar.mutate(fila.id)}>
                                            Volver a publicar
                                        </Boton>
                                    )}
                                </div>
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

            {resenaAResponder && (
                <ModalResponderResena
                    resena={resenaAResponder}
                    enviando={mutacionResponder.isPending}
                    onGuardar={(respuesta) => mutacionResponder.mutateAsync(respuesta)}
                    onCerrar={() => setResenaAResponder(null)}
                />
            )}
        </LayoutPanelNegocio>
    );
}
