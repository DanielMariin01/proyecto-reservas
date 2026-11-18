import type { ReactElement } from 'react';
import { useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import { obtenerHistorialClinico } from './api/historialClinico';

export default function SeccionHistorialClinico(): ReactElement {
    const consulta = useQuery({ queryKey: ['mi-cuenta-historial-clinico'], queryFn: obtenerHistorialClinico });

    if (consulta.isLoading) {
        return (
            <div className="space-y-4">
                <div className="h-32 animate-pulse rounded-tarjeta bg-gris-claro" />
                <div className="h-32 animate-pulse rounded-tarjeta bg-gris-claro" />
            </div>
        );
    }

    if (consulta.isError) {
        return (
            <div className="rounded-tarjeta bg-blanco p-6 text-center shadow-tarjeta">
                <p className="text-sm text-texto-suave">Algo falló cargando tu historial clínico. Código REF-HCC1.</p>
                <Boton variante="contorno" className="mt-4" onClick={() => consulta.refetch()}>
                    Reintentar
                </Boton>
            </div>
        );
    }

    const historias = consulta.data ?? [];

    if (historias.length === 0) {
        return <p className="text-sm text-texto-suave">Todavía no tienes historial clínico registrado.</p>;
    }

    return (
        <div className="space-y-4">
            {historias.map((historia) => (
                <Tarjeta key={historia.id} className="space-y-3">
                    <div className="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p className="font-semibold text-tinta">{historia.nombreNegocio}</p>
                            <p className="text-sm text-texto-suave">{historia.fecha}</p>
                        </div>
                        {historia.profesionalNombre && <p className="text-sm text-texto-suave">Atendió: {historia.profesionalNombre}</p>}
                    </div>

                    <div className="grid gap-1 text-sm text-tinta">
                        <p>
                            <span className="text-texto-suave">Servicio:</span> {historia.servicioDiseno}
                        </p>
                        <p>
                            <span className="text-texto-suave">Forma/largo:</span> {historia.formaUna} · {historia.largoUna}
                        </p>
                        {historia.colores && (
                            <p>
                                <span className="text-texto-suave">Color(es):</span> {historia.colores}
                            </p>
                        )}
                        {historia.decoracionReferencia && (
                            <p>
                                <span className="text-texto-suave">Decoración:</span> {historia.decoracionReferencia}
                            </p>
                        )}
                        {historia.observacionesValoracion && (
                            <p>
                                <span className="text-texto-suave">Observaciones:</span> {historia.observacionesValoracion}
                            </p>
                        )}
                        {historia.novedadesIncidentes && (
                            <p>
                                <span className="text-texto-suave">Novedades:</span> {historia.novedadesIncidentes}
                            </p>
                        )}
                        {historia.proximoMantenimientoSugerido && (
                            <p>
                                <span className="text-texto-suave">Próximo mantenimiento sugerido:</span> {historia.proximoMantenimientoSugerido}
                            </p>
                        )}
                    </div>

                    {(historia.urlFirmaClienta || historia.urlFirmaProfesional) && (
                        <div className="grid grid-cols-2 gap-3">
                            {historia.urlFirmaClienta && (
                                <div>
                                    <p className="mb-1 text-xs text-texto-suave">Tu firma</p>
                                    <img src={historia.urlFirmaClienta} alt="Firma de la clienta" className="h-20 rounded-boton border border-gris-claro object-contain" />
                                </div>
                            )}
                            {historia.urlFirmaProfesional && (
                                <div>
                                    <p className="mb-1 text-xs text-texto-suave">Firma de la profesional</p>
                                    <img
                                        src={historia.urlFirmaProfesional}
                                        alt="Firma de la profesional"
                                        className="h-20 rounded-boton border border-gris-claro object-contain"
                                    />
                                </div>
                            )}
                        </div>
                    )}
                </Tarjeta>
            ))}
        </div>
    );
}
