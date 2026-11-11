import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { desvincularServicio, obtenerServiciosDelProfesional, vincularServicio } from '../api/profesionalServicios';
import ModalAttachServicio from './ModalAttachServicio';

interface Propiedades {
    slug: string;
    profesionalId: number;
}

export default function SeccionServiciosVinculados({ slug, profesionalId }: Propiedades): ReactElement {
    const [modalAbierto, setModalAbierto] = useState(false);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-profesional-servicios', slug, profesionalId],
        queryFn: () => obtenerServiciosDelProfesional(slug, profesionalId),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-profesional-servicios', slug, profesionalId] });
    }

    const mutacionVincular = useMutation({
        mutationFn: (datos: { servicio_id: number; precio: number | null; duracion_minutos: number | null }) =>
            vincularServicio(slug, profesionalId, datos),
        onSuccess: () => {
            setModalAbierto(false);
            invalidar();
        },
    });

    const mutacionDesvincular = useMutation({
        mutationFn: (servicioId: number) => desvincularServicio(slug, profesionalId, servicioId),
        onSuccess: invalidar,
    });

    const disponibles = consulta.data?.disponibles ?? [];

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between gap-4">
                <h3 className="text-sm font-semibold text-tinta">Servicios que presta</h3>
                {disponibles.length > 0 && (
                    <Boton variante="contorno" onClick={() => setModalAbierto(true)}>
                        Vincular servicio
                    </Boton>
                )}
            </div>

            <TablaPaginada
                columnas={[
                    { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                    { clave: 'precio', etiqueta: 'Precio propio', render: (fila) => (fila.precioPropio === null ? 'Usa el del servicio' : `$${fila.precioPropio.toLocaleString('es-CO')}`) },
                    {
                        clave: 'duracion',
                        etiqueta: 'Duración propia (min)',
                        render: (fila) => (fila.duracionPropiaMinutos === null ? 'Usa la del servicio' : fila.duracionPropiaMinutos),
                    },
                    {
                        clave: 'acciones',
                        etiqueta: '',
                        render: (fila) => (
                            <Boton
                                variante="texto"
                                disabled={mutacionDesvincular.isPending}
                                onClick={() => {
                                    if (window.confirm(`¿Desvincular "${fila.nombre}"?`)) {
                                        mutacionDesvincular.mutate(fila.id);
                                    }
                                }}
                            >
                                Desvincular
                            </Boton>
                        ),
                    },
                ]}
                filas={consulta.data?.vinculados ?? []}
                idDeFila={(fila) => fila.id}
                cargando={consulta.isLoading}
                error={consulta.isError}
                mensajeVacio="Todavía no presta ningún servicio."
                codigoError="REF-PRS0"
                onReintentar={() => consulta.refetch()}
                paginaActual={1}
                totalPaginas={1}
                onCambiarPagina={() => {}}
            />

            {modalAbierto && (
                <ModalAttachServicio
                    disponibles={disponibles}
                    enviando={mutacionVincular.isPending}
                    onGuardar={(datos) => mutacionVincular.mutateAsync(datos)}
                    onCerrar={() => setModalAbierto(false)}
                />
            )}
        </div>
    );
}
