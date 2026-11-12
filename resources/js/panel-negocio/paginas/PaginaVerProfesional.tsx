import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import { actualizarHorarioSemanal, type DiasHorario, obtenerVistaPreviaDisponibilidad } from '../api/horarioSemanal';
import type { Profesional } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import ModalEditarHorarioSemanal from './ModalEditarHorarioSemanal';
import ModalVistaPreviaDisponibilidad from './ModalVistaPreviaDisponibilidad';
import SeccionBloqueos from './SeccionBloqueos';
import SeccionServiciosVinculados from './SeccionServiciosVinculados';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    profesional: Profesional;
}

export default function PaginaVerProfesional({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, profesional }: Propiedades): ReactElement {
    const [modalHorarioAbierto, setModalHorarioAbierto] = useState(false);
    const [modalVistaPreviaAbierto, setModalVistaPreviaAbierto] = useState(false);

    const mutacionGuardarHorario = useMutation({
        mutationFn: (dias: DiasHorario) => actualizarHorarioSemanal(negocioSlug, profesional.id, dias),
        onSuccess: () => setModalHorarioAbierto(false),
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">{profesional.nombre}</h2>
                    <div className="flex flex-wrap gap-2">
                        <Boton variante="contorno" onClick={() => setModalVistaPreviaAbierto(true)}>
                            Vista previa de disponibilidad
                        </Boton>
                        <Boton variante="contorno" onClick={() => setModalHorarioAbierto(true)}>
                            Editar horario
                        </Boton>
                        <Boton onClick={() => (window.location.href = `/app/${negocioSlug}/profesionales/${profesional.id}/edit`)}>Editar</Boton>
                    </div>
                </div>

                <Tarjeta className="grid gap-4 sm:grid-cols-2">
                    <div className="flex items-center gap-4">
                        {profesional.foto ? (
                            <img src={profesional.foto} alt="" className="h-16 w-16 rounded-full object-cover" />
                        ) : (
                            <div className="h-16 w-16 rounded-full bg-gris-claro" />
                        )}
                        <div>
                            <p className="font-semibold text-tinta">{profesional.nombre}</p>
                            <p className="text-sm text-texto-suave">{profesional.especialidad ?? '—'}</p>
                        </div>
                    </div>
                    <div>
                        <p className="text-sm font-medium text-tinta">{profesional.activo ? 'Activo' : 'Inactivo'}</p>
                        <p className="text-sm text-texto-suave">Usuario vinculado: {profesional.usuarioEmail ?? 'Sin vincular'}</p>
                    </div>
                    {profesional.biografia && (
                        <div className="sm:col-span-2">
                            <p className="text-sm font-medium text-tinta">Biografía</p>
                            <p className="text-sm text-texto-suave">{profesional.biografia}</p>
                        </div>
                    )}
                    {profesional.certificaciones && (
                        <div className="sm:col-span-2">
                            <p className="text-sm font-medium text-tinta">Certificaciones</p>
                            <p className="text-sm text-texto-suave">{profesional.certificaciones}</p>
                        </div>
                    )}
                </Tarjeta>

                <SeccionServiciosVinculados slug={negocioSlug} profesionalId={profesional.id} />
                <SeccionBloqueos slug={negocioSlug} profesionalId={profesional.id} />
            </div>

            {modalHorarioAbierto && (
                <ModalEditarHorarioSemanal
                    slug={negocioSlug}
                    profesionalId={profesional.id}
                    enviando={mutacionGuardarHorario.isPending}
                    onGuardar={(dias) => mutacionGuardarHorario.mutateAsync(dias)}
                    onCerrar={() => setModalHorarioAbierto(false)}
                />
            )}

            {modalVistaPreviaAbierto && (
                <ModalVistaPreviaDisponibilidad
                    slug={negocioSlug}
                    onConsultar={(servicioId, fecha) => obtenerVistaPreviaDisponibilidad(negocioSlug, profesional.id, servicioId, fecha)}
                    onCerrar={() => setModalVistaPreviaAbierto(false)}
                />
            )}
        </LayoutPanelNegocio>
    );
}
