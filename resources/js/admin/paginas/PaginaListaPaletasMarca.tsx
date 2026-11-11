import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import { eliminarPaletaMarca, obtenerPaletasMarca } from '../api/paletasMarca';
import TablaPaginada from '../componentes/TablaPaginada';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaListaPaletasMarca({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [buscar, setBuscar] = useState('');
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['admin-paletas-marca', pagina, buscar],
        queryFn: () => obtenerPaletasMarca(pagina, buscar),
    });

    const mutacionEliminar = useMutation({
        mutationFn: eliminarPaletaMarca,
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-paletas-marca'] }),
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Paletas de marca</h2>
                    <Boton onClick={() => (window.location.href = '/admin/paleta-marcas/create')}>Nueva paleta</Boton>
                </div>

                <CampoTexto
                    etiqueta="Buscar por nombre"
                    value={buscar}
                    onChange={(evento) => {
                        setPagina(1);
                        setBuscar(evento.target.value);
                    }}
                />

                <TablaPaginada
                    columnas={[
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        {
                            clave: 'primario',
                            etiqueta: 'Primario',
                            render: (fila) => (
                                <span className="inline-flex items-center gap-2">
                                    <span className="h-6 w-6 rounded-boton border border-gris-claro" style={{ backgroundColor: fila.colorPrimario }} />
                                    {fila.colorPrimario}
                                </span>
                            ),
                        },
                        {
                            clave: 'secundario',
                            etiqueta: 'Secundario',
                            render: (fila) => (
                                <span className="inline-flex items-center gap-2">
                                    <span
                                        className="h-6 w-6 rounded-boton border border-gris-claro"
                                        style={{ backgroundColor: fila.colorSecundario }}
                                    />
                                    {fila.colorSecundario}
                                </span>
                            ),
                        },
                        { clave: 'negocios', etiqueta: 'Negocios', render: (fila) => fila.negociosCount },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <div className="flex gap-2">
                                    <Boton variante="contorno" onClick={() => (window.location.href = `/admin/paleta-marcas/${fila.id}/edit`)}>
                                        Editar
                                    </Boton>
                                    <Boton
                                        variante="texto"
                                        disabled={mutacionEliminar.isPending}
                                        onClick={() => {
                                            if (window.confirm(`¿Eliminar la paleta "${fila.nombre}"?`)) {
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
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no hay paletas de marca. Crea la primera."
                    codigoError="REF-PAL1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutAdmin>
    );
}
