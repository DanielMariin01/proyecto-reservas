import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Banknote, Building2 } from 'lucide-react';
import Boton from '@/componentes/Boton';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import TablaPaginada from '@/componentes/TablaPaginada';
import { obtenerNegocio, obtenerPagosSuscripcion } from '../api/negocios';
import { obtenerRegistrosAuditoria } from '../api/registroAuditorias';
import LayoutAdmin from '../LayoutAdmin';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
    slug: string;
}

export default function PaginaVerNegocio({ urlSalir, tokenCsrf, slug }: Propiedades): ReactElement {
    const [paginaPagos, setPaginaPagos] = useState(1);
    const [paginaAuditoria, setPaginaAuditoria] = useState(1);

    const consulta = useQuery({ queryKey: ['admin-negocio', slug], queryFn: () => obtenerNegocio(slug) });

    const pagos = useQuery({
        queryKey: ['admin-negocio-pagos', slug, paginaPagos],
        queryFn: () => obtenerPagosSuscripcion(slug, paginaPagos),
        enabled: consulta.isSuccess,
    });

    const auditoria = useQuery({
        queryKey: ['admin-negocio-auditoria', slug, paginaAuditoria],
        queryFn: () => obtenerRegistrosAuditoria(paginaAuditoria, '', consulta.data?.id),
        enabled: consulta.isSuccess,
    });

    if (consulta.isLoading) {
        return (
            <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
                <div className="h-96 animate-pulse rounded-tarjeta bg-gris-claro" />
            </LayoutAdmin>
        );
    }

    if (consulta.isError || !consulta.data) {
        return (
            <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
                <p className="text-sm text-texto-suave">Algo falló cargando el negocio. Código REF-NEG2.</p>
            </LayoutAdmin>
        );
    }

    const negocio = consulta.data;

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">{negocio.nombre}</h2>
                    <Boton onClick={() => (window.location.href = `/admin/negocios/${slug}/edit`)}>Editar</Boton>
                </div>

                <Tarjeta className="grid gap-4 sm:grid-cols-3">
                    <LineaDetalle icono={Building2} texto={`${negocio.tipoEtiqueta} · ${negocio.slug}`} />
                    <LineaDetalle icono={Banknote} texto={`Próximo pago: ${negocio.proximaFechaPago ?? '—'}`} />
                    <div>
                        <p className="text-sm font-medium text-tinta">{negocio.estadoSuscripcionEtiqueta}</p>
                        {negocio.fechaLimiteRetencionDatos && (
                            <p className="text-sm text-texto-suave">Datos se conservan hasta {negocio.fechaLimiteRetencionDatos}</p>
                        )}
                    </div>
                </Tarjeta>

                <Tarjeta>
                    <h3 className="mb-3 text-sm font-semibold text-tinta">Usuarios</h3>
                    {negocio.usuarios.length === 0 ? (
                        <p className="text-sm text-texto-suave">Este negocio todavía no tiene usuarios con rol asignado.</p>
                    ) : (
                        <ul className="list-disc space-y-1 pl-5 text-sm text-tinta">
                            {negocio.usuarios.map((usuario) => (
                                <li key={usuario.email}>
                                    {usuario.nombre} ({usuario.rol}) — {usuario.email}
                                </li>
                            ))}
                        </ul>
                    )}
                </Tarjeta>

                <div>
                    <h3 className="mb-3 text-sm font-semibold text-tinta">Pagos de suscripción</h3>
                    <TablaPaginada
                        columnas={[
                            { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.fecha },
                            { clave: 'valor', etiqueta: 'Valor', render: (fila) => `$${fila.valor.toLocaleString('es-CO')}` },
                            { clave: 'metodo', etiqueta: 'Método', render: (fila) => fila.metodo },
                            { clave: 'periodo', etiqueta: 'Período', render: (fila) => `${fila.periodoDesde} – ${fila.periodoHasta}` },
                            { clave: 'registradoPor', etiqueta: 'Registrado por', render: (fila) => fila.registradoPor ?? '—' },
                        ]}
                        filas={pagos.data?.datos ?? []}
                        idDeFila={(fila) => fila.id}
                        cargando={pagos.isLoading}
                        error={pagos.isError}
                        mensajeVacio="Todavía no hay pagos registrados."
                        codigoError="REF-NEG3"
                        onReintentar={() => pagos.refetch()}
                        paginaActual={pagos.data?.paginaActual ?? paginaPagos}
                        totalPaginas={pagos.data?.totalPaginas ?? 1}
                        onCambiarPagina={setPaginaPagos}
                    />
                </div>

                <div>
                    <h3 className="mb-3 text-sm font-semibold text-tinta">Últimos cambios</h3>
                    <TablaPaginada
                        columnas={[
                            { clave: 'fecha', etiqueta: 'Fecha', render: (fila) => fila.fecha },
                            { clave: 'accion', etiqueta: 'Acción', render: (fila) => fila.accion },
                            { clave: 'usuario', etiqueta: 'Usuario', render: (fila) => fila.usuario },
                            { clave: 'soporte', etiqueta: 'Soporte', render: (fila) => (fila.hechoPorSoporte ? 'Sí' : 'No') },
                            { clave: 'motivo', etiqueta: 'Motivo', render: (fila) => fila.motivo ?? '—' },
                        ]}
                        filas={auditoria.data?.datos ?? []}
                        idDeFila={(fila) => fila.id}
                        cargando={auditoria.isLoading}
                        error={auditoria.isError}
                        mensajeVacio="Todavía no hay cambios registrados."
                        codigoError="REF-NEG4"
                        onReintentar={() => auditoria.refetch()}
                        paginaActual={auditoria.data?.paginaActual ?? paginaAuditoria}
                        totalPaginas={auditoria.data?.totalPaginas ?? 1}
                        onCambiarPagina={setPaginaAuditoria}
                    />
                </div>
            </div>
        </LayoutAdmin>
    );
}
