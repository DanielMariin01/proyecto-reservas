import type { ReactElement } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { actualizarNegocio, type DatosCrearNegocio, type DatosNegocio, eliminarNegocio, obtenerNegocio } from '../api/negocios';
import FormularioNegocio from './FormularioNegocio';
import LayoutAdmin from '../LayoutAdmin';
import SeccionPaginaWeb from './SeccionPaginaWeb';

interface Propiedades {
    urlSalir: string;
    tokenCsrf: string;
    slug: string;
}

export default function PaginaEditarNegocio({ urlSalir, tokenCsrf, slug }: Propiedades): ReactElement {
    const consulta = useQuery({ queryKey: ['admin-negocio', slug], queryFn: () => obtenerNegocio(slug) });

    const mutacion = useMutation({
        mutationFn: (datos: DatosNegocio) => actualizarNegocio(slug, datos),
        onSuccess: () => {
            window.location.href = '/admin/negocios';
        },
    });

    const mutacionEliminar = useMutation({
        mutationFn: () => eliminarNegocio(slug),
        onSuccess: () => {
            window.location.href = '/admin/negocios';
        },
    });

    return (
        <LayoutAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Editar negocio</h2>
                    <div className="flex gap-2">
                        <Boton variante="contorno" onClick={() => (window.location.href = `/admin/negocios/${slug}`)}>
                            Ver
                        </Boton>
                        <Boton
                            variante="texto"
                            disabled={mutacionEliminar.isPending}
                            onClick={() => {
                                if (window.confirm('¿Eliminar este negocio? Esta acción no se puede deshacer.')) {
                                    mutacionEliminar.mutate();
                                }
                            }}
                        >
                            Eliminar
                        </Boton>
                    </div>
                </div>

                {consulta.isLoading && <div className="h-96 animate-pulse rounded-tarjeta bg-gris-claro" />}

                {consulta.isError && <p className="text-sm text-texto-suave">Algo falló cargando el negocio. Código REF-NEG1.</p>}

                {consulta.data && (
                    <FormularioNegocio
                        esCreacion={false}
                        valoresIniciales={consulta.data}
                        enviando={mutacion.isPending}
                        textoBoton="Guardar cambios"
                        onGuardar={(datos: DatosNegocio | DatosCrearNegocio) => mutacion.mutateAsync(datos as DatosNegocio)}
                    />
                )}

                <SeccionPaginaWeb slug={slug} />
            </div>
        </LayoutAdmin>
    );
}
