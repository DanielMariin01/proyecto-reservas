import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarProfesional, type DatosCrearProfesional, type DatosProfesional, eliminarProfesional, type Profesional } from '../api/profesionales';
import LayoutPanelNegocio from '../LayoutPanelNegocio';
import FormularioProfesional from './FormularioProfesional';

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
    profesional: Profesional;
}

export default function PaginaEditarProfesional({ negocioNombre, negocioSlug, urlSalir, tokenCsrf, profesional }: Propiedades): ReactElement {
    const mutacion = useMutation({
        mutationFn: (datos: DatosProfesional) => actualizarProfesional(negocioSlug, profesional.id, datos),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/profesionales`;
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarProfesional(negocioSlug, profesional.id),
        onSuccess: () => {
            window.location.href = `/app/${negocioSlug}/profesionales`;
        },
    });

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar profesional</h2>
                    <div className="flex gap-2">
                        <Boton variante="contorno" onClick={() => (window.location.href = `/app/${negocioSlug}/profesionales/${profesional.id}`)}>
                            Ver
                        </Boton>
                        <Boton
                            variante="texto"
                            disabled={mutacionEliminar.isPending}
                            onClick={() => {
                                if (window.confirm('¿Eliminar este profesional?')) {
                                    mutacionEliminar.mutate();
                                }
                            }}
                        >
                            Eliminar
                        </Boton>
                    </div>
                </div>
                <FormularioProfesional
                    slug={negocioSlug}
                    esCreacion={false}
                    valoresIniciales={profesional}
                    enviando={mutacion.isPending}
                    textoBoton="Guardar cambios"
                    onGuardar={(datos: DatosProfesional | DatosCrearProfesional) => mutacion.mutateAsync(datos as DatosProfesional)}
                />
            </div>
        </LayoutPanelNegocio>
    );
}
