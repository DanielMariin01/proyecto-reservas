import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import CampoTexto from '@/componentes/CampoTexto';
import TablaPaginada from '@/componentes/TablaPaginada';
import { obtenerRegistrosAuditoria } from '../api/registroAuditorias';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaRegistroAuditorias({ urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [pagina, setPagina] = useState(1);
    const [buscar, setBuscar] = useState('');

    const consulta = useQuery({
        queryKey: ['admin-registro-auditorias', pagina, buscar],
        queryFn: () => obtenerRegistrosAuditoria(pagina, buscar),
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <h2 className="text-lg font-semibold text-tinta">Registro de auditoría</h2>

                <CampoTexto
                    etiqueta="Buscar por acción"
                    value={buscar}
                    onChange={(evento) => {
                        setPagina(1);
                        setBuscar(evento.target.value);
                    }}
                />

                <TablaPaginada
                    columnas={[
                        { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.fecha },
                        { clave: 'negocio', etiqueta: 'Negocio', render: (fila) => fila.negocio ?? '—' },
                        { clave: 'usuario', etiqueta: 'Usuario', render: (fila) => fila.usuario },
                        { clave: 'accion', etiqueta: 'Acción', render: (fila) => fila.accion },
                        { clave: 'soporte', etiqueta: 'Soporte', render: (fila) => (fila.hechoPorSoporte ? 'Sí' : 'No') },
                        { clave: 'motivo', etiqueta: 'Motivo', render: (fila) => fila.motivo ?? '—' },
                    ]}
                    filas={consulta.data?.datos ?? []}
                    idDeFila={(fila) => fila.id}
                    cargando={consulta.isLoading}
                    error={consulta.isError}
                    mensajeVacio="Todavía no hay registros de auditoría."
                    codigoError="REF-AUD1"
                    onReintentar={() => consulta.refetch()}
                    paginaActual={consulta.data?.paginaActual ?? pagina}
                    totalPaginas={consulta.data?.totalPaginas ?? 1}
                    onCambiarPagina={setPagina}
                />
            </div>
        </LayoutAdmin>
    );
}
