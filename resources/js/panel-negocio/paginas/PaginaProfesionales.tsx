import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { eliminarProfesional, obtenerProfesionales, type Profesional, vincularUsuario } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalVincularUsuario from './ModalVincularUsuario';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaProfesionales({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [profesionalAVincular, setProfesionalAVincular] = useState<Profesional | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-profesionales', negocioSlug],
        queryFn: () => obtenerProfesionales(negocioSlug),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-profesionales', negocioSlug] });
    }

    const mutacionEliminar = useMutation({
        mutationFn: (id: number) => eliminarProfesional(negocioSlug, id),
        onSuccess: invalidar,
    });

    const mutacionVincular = useMutation({
        mutationFn: (datos: { nombre: string; email: string }) => vincularUsuario(negocioSlug, profesionalAVincular!.id, datos),
        onSuccess: () => {
            setProfesionalAVincular(null);
            invalidar();
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Profesionales</h2>
                    <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/profesionales/create`)}>Nuevo profesional</Boton>
                </div>

                <TablaPaginada
                    columnas={[
                        {
                            clave: 'foto',
                            etiqueta: '',
                            render: (fila) =>
                                fila.foto ? (
                                    <img src={fila.foto} alt="" className="h-10 w-10 rounded-full object-cover" />
                                ) : (
                                    <div className="h-10 w-10 rounded-full bg-gris-claro" />
                                ),
                        },
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'especialidad', etiqueta: 'Especialidad', render: (fila) => fila.especialidad ?? '—' },
                        { clave: 'usuario', etiqueta: 'Usuario', render: (fila) => fila.usuarioEmail ?? 'Sin vincular' },
                        { clave: 'activo', etiqueta: 'Activo', render: (fila) => (fila.activo ? 'Sí' : 'No') },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex flex-wrap gap-2">
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/app/${negocioSlug}/profesionales/${fila.id}`)}>
                                        Ver
                                    </Boton>
                                    <Boton
                                        variante="contorno"
                                        onClick={() => (window.location.href = `/app/${negocioSlug}/profesionales/${fila.id}/edit`)}
                                    >
                                        Editar
                                    </Boton>
                                    {fila.usuarioEmail === null && (
                                        <Boton variante="texto" onClick={() => setProfesionalAVincular(fila)}>
                                            Vincular usuario
                                        </Boton>
                                    )}
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminar.isPending}
                                        onClick={() => {
                                            if (window.confirm(`¿Eliminar a "${fila.nombre}"?`)) {
                                                mutacionEliminar.mutate(fila.id);
                                            }
                                        }}
                                    >
                                        Eliminar
                                    </Boton>
                                </div>
                            ),
                        },
                    ]}
                    filas={consulta.data ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no tienes profesionales."
                    codigoError="REF-PRO0"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={1}
                    totalPaginas={1}
                    onCambiarPagina={() => {}}
                />
            </div>

            {profesionalAVincular && (
                <ModalVincularUsuario
                    enviando={mutacionVincular.isPending}
                    onGuardar={(datos) => mutacionVincular.mutateAsync(datos)}
                    onCerrar={() => setProfesionalAVincular(null)}
                />
            )}
        </LayoutPanelNegocio>
    );
}
