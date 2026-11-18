import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import CampoTexto from '@/componentes/CampoTexto';
import TablaPaginada from '@/componentes/TablaPaginada';
import { obtenerClientes } from '../api/clientes';
import LayoutPanelNegocio from '../LayoutPanelNegocio';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaClientes({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [buscar, setBuscar] = useState('');
    const [pagina, setPagina] = useState(1);

    const consulta = useQuery({
        queryKey: ['panel-clientes', negocioSlug, buscar, pagina],
        queryFn: () => obtenerClientes(negocioSlug, buscar, pagina),
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Clientes</h2>

                <CampoTexto
                    etiqueta="Buscar por nombre o celular"
                    value={buscar}
                    onChange={(evento) => {
                        setPagina(1);
                        setBuscar(evento.target.value);
                    }}
                    className="max-w-sm"
                />

                <TablaPaginada
                    columnas={[
                        { clave: 'codigo', etiqueta: 'Código', render: (fila) => fila.codigoClienta },
                        { clave: 'nombre', etiqueta: 'Nombre', render: (fila) => fila.nombre },
                        { clave: 'celular', etiqueta: 'Celular', render: (fila) => fila.celular },
                        { clave: 'citas', etiqueta: 'Citas', render: (fila) => fila.citasCount ?? 0 },
                        {
                            clave: 'acciones',
                            etiqueta: '',
                            render: (fila) => (
                                <Boton variante="contorno" onClick={() => (window.location.href = `/app/${negocioSlug}/clientes/${fila.id}`)}>
                                    Ver ficha
                                </Boton>
                            ),
                        },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no tienes clientes registrados."
                    codigoError="REF-CLI1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? 1}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
