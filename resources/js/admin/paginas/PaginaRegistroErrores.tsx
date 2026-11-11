import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import CampoTexto from '@/componentes/CampoTexto';
import TablaPaginada from '@/componentes/TablaPaginada';
import { obtenerOpcionesNegocio, obtenerRegistrosError } from '../api/registroErrores';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaRegistroErrores({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [buscar, setBuscar] = useState('');
    const [negocioId, setNegocioId] = useState<number | null>(null);
    const [ultimas24h, setUltimas24h] = useState(true);

    const opcionesNegocio = useQuery({ queryKey: ['admin-negocios-opciones'], queryFn: obtenerOpcionesNegocio });

    const consulta = useQuery({
        queryKey: ['admin-registro-errores', pagina, buscar, negocioId, ultimas24h],
        queryFn: () => obtenerRegistrosError({ pagina, buscar, negocioId, ultimas24h }),
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Registro de errores</h2>

                <div className="flex flex-wrap items-end gap-4">
                    <CampoTexto
                        etiqueta="Buscar por excepción"
                        value={buscar}
                        onChange={(evento) => {
                            setPagina(1);
                            setBuscar(evento.target.value);
                        }}
                    />

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

                    <label className="flex h-12 items-center gap-2 text-sm text-tinta">
                        <input
                            type="checkbox"
                            checked={ultimas24h}
                            onChange={(evento) => {
                                setPagina(1);
                                setUltimas24h(evento.target.checked);
                            }}
                        />
                        Últimas 24 horas
                    </label>
                </div>

                <TablaPaginada
                    columnas={[
                        { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.fecha },
                        { clave: 'negocio', etiqueta: 'Negocio', render: (fila) => fila.negocio ?? '—' },
                        { clave: 'usuario', etiqueta: 'Usuario', render: (fila) => fila.usuario },
                        { clave: 'excepcion', etiqueta: 'Excepción', render: (fila) => fila.claseExcepcion },
                        { clave: 'mensaje', etiqueta: 'Mensaje', render: (fila) => fila.mensaje },
                        {
                            clave: 'codigo',
                            etiqueta: 'Código',
                            render: (fila) => (
                                <span className="rounded-boton bg-gris-claro px-2 py-1 text-xs font-semibold text-tinta">
                                    {fila.codigoReferencia}
                                </span>
                            ),
                        },
                        { clave: 'http', etiqueta: 'HTTP', render: (fila) => fila.estadoHttp },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="No hay errores registrados con estos filtros."
                    codigoError="REF-ERR1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutAdmin>
    );
}
