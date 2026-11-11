import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import TablaPaginada from '@/componentes/TablaPaginada';
import { actualizarBloqueo, type Bloqueo, crearBloqueo, type DatosBloqueo, eliminarBloqueo, obtenerBloqueos } from '../api/bloqueos';
import ModalBloqueo from './ModalBloqueo';

interface Propiedades {
    slug: string;
    profesionalId: number;
}

export default function SeccionBloqueos({ slug, profesionalId }: Propiedades): ReactElement {
    const [modalAbierto, setModalAbierto] = useState(false);
    const [bloqueoAEditar, setBloqueoAEditar] = useState<Bloqueo | null>(null);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-bloqueos', slug, profesionalId],
        queryFn: () => obtenerBloqueos(slug, profesionalId),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-bloqueos', slug, profesionalId] });
    }

    function cerrarModal(): void {
        setModalAbierto(false);
        setBloqueoAEditar(null);
    }

    const mutacionGuardar = useMutation({
        mutationFn: (datos: DatosBloqueo) =>
            bloqueoAEditar
                ? actualizarBloqueo(slug, profesionalId, bloqueoAEditar.id, datos)
                : crearBloqueo(slug, profesionalId, datos),
        onSuccess: () => {
            cerrarModal();
            invalidar();
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: (id: number) => eliminarBloqueo(slug, profesionalId, id),
        onSuccess: invalidar,
    });

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between gap-4">
                <h3 className="text-sm font-semibold text-tinta">Bloqueos y aperturas especiales</h3>
                <Boton variante="contorno" onClick={() => setModalAbierto(true)}>
                    Nuevo bloqueo
                </Boton>
            </div>

            <TablaPaginada
                columnas={[
                    { clave: 'tipo', etiqueta: 'Tipo', render: (fila) => fila.tipoEtiqueta },
                    { clave: 'desde', etiqueta: 'Desde', render: (fila) => `${fila.fechaInicio} ${fila.horaInicio}` },
                    { clave: 'hasta', etiqueta: 'Hasta', render: (fila) => `${fila.fechaFin} ${fila.horaFin}` },
                    { clave: 'motivo', etiqueta: 'Motivo', render: (fila) => fila.motivo ?? '—' },
                    {
                        clave: 'acciones',
                        etiqueta: '',
                        render: (fila) => (
                            <div className="flex gap-2">
                                <Boton
                                    variante="contorno"
                                    onClick={() => {
                                        setBloqueoAEditar(fila);
                                        setModalAbierto(true);
                                    }}
                                >
                                    Editar
                                </Boton>
                                <Boton
                                    variante="texto"
                                    disabled={mutacionEliminar.isPending}
                                    onClick={() => {
                                        if (window.confirm('¿Eliminar este bloqueo?')) {
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
                mensajeVacio="Todavía no hay bloqueos."
                codigoError="REF-BLQ0"
                onReintentar={() => consulta.refetch()}
                paginaActual={1}
                totalPaginas={1}
                onCambiarPagina={() => {}}
            />

            {modalAbierto && (
                <ModalBloqueo
                    bloqueo={bloqueoAEditar ?? undefined}
                    enviando={mutacionGuardar.isPending}
                    onGuardar={(datos) => mutacionGuardar.mutateAsync(datos)}
                    onCerrar={cerrarModal}
                />
            )}
        </div>
    );
}
