import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { obtenerTrabajosFallidos, reintentarTrabajoFallido } from '../api/trabajosFallidos';
import TablaPaginada from '../componentes/TablaPaginada';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaTrabajosFallidos({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['admin-trabajos-fallidos', pagina],
        queryFn: () => obtenerTrabajosFallidos(pagina),
    });

    const mutacionReintentar = useMutation({
        mutationFn: reintentarTrabajoFallido,
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-trabajos-fallidos'] }),
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Trabajos fallidos</h2>

                <TablaPaginada
                    columnas={[
                        { clave: 'fallo', etiqueta: 'Falló', render: (fila) => fila.falloEn },
                        { clave: 'cola', etiqueta: 'Cola', render: (fila) => fila.cola },
                        {
                            clave: 'tipo',
                            etiqueta: 'Tipo',
                            render: (fila) => (
                                <span className="rounded-boton bg-gris-claro px-2 py-1 text-xs font-semibold text-tinta">{fila.tipo}</span>
                            ),
                        },
                        { clave: 'excepcion', etiqueta: 'Excepción', render: (fila) => fila.excepcion },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <Boton
                                    variante="contorno"
                                    disabled={mutacionReintentar.isPending}
                                    onClick={() => {
                                        if (window.confirm('¿Reintentar este trabajo?')) {
                                            mutacionReintentar.mutate(fila.id);
                                        }
                                    }}
                                >
                                    Reintentar
                                </Boton>
                            ),
                        },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="No hay trabajos fallidos."
                    codigoError="REF-JOB1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutAdmin>
    );
}
