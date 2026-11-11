import type { ReactElement } from 'react';
import { useEffect, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Eye, Rocket } from 'lucide-react';
import Boton from '@/componentes/Boton';
import Tarjeta from '@/componentes/Tarjeta';
import { actualizarBloquesPagina, type Bloque, ErrorDeValidacion, obtenerPaginaWeb, publicarPagina } from '../api/paginaWeb';
import BuilderBloques from './bloques/BuilderBloques';

interface Propiedades {
    slug: string;
}

export default function SeccionPaginaWeb({ slug }: Propiedades): ReactElement {
    const queryClient = useQueryClient();
    const consulta = useQuery({ queryKey: ['admin-negocio-pagina', slug], queryFn: () => obtenerPaginaWeb(slug) });
    const [bloques, setBloques] = useState<Bloque[]>([]);
    const [errorGeneral, setErrorGeneral] = useState<string | null>(null);

    useEffect(() => {
        if (consulta.data) {
            setBloques(consulta.data.bloques);
        }
    }, [consulta.data]);

    const mutacionGuardar = useMutation({
        mutationFn: () => actualizarBloquesPagina(slug, bloques),
        onSuccess: (pagina) => {
            setErrorGeneral(null);
            queryClient.setQueryData(['admin-negocio-pagina', slug], pagina);
        },
        onError: (error: unknown) => {
            setErrorGeneral(error instanceof ErrorDeValidacion ? Object.values(error.errores).flat().join(' ') : 'No se pudieron guardar los bloques.');
        },
    });

    const mutacionPublicar = useMutation({
        mutationFn: () => publicarPagina(slug),
        onSuccess: (pagina) => queryClient.setQueryData(['admin-negocio-pagina', slug], pagina),
    });

    if (consulta.isLoading) {
        return <div className="h-64 animate-pulse rounded-tarjeta bg-gris-claro" />;
    }

    if (consulta.isError) {
        return <p className="text-sm text-texto-suave">Algo falló cargando la página web. Código REF-NEG5.</p>;
    }

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 className="text-lg font-semibold text-tinta">Página web</h2>
                    <p className="text-sm text-texto-suave">
                        Agrega, quita y reordena los bloques. El dueño solo puede editar los textos y fotos de los que dejes aquí, si lo
                        permites en los datos del negocio.
                    </p>
                </div>

                <div className="flex gap-2">
                    <a href={`/${slug}?vista_previa=1`} target="_blank" rel="noreferrer">
                        <Boton variante="contorno" className="flex items-center gap-2">
                            <Eye size={16} strokeWidth={1.75} />
                            Vista previa
                        </Boton>
                    </a>
                    <Boton
                        variante="contorno"
                        disabled={mutacionPublicar.isPending}
                        onClick={() => mutacionPublicar.mutate()}
                        className="flex items-center gap-2"
                    >
                        <Rocket size={16} strokeWidth={1.75} />
                        Publicar página
                    </Boton>
                </div>
            </div>

            {consulta.data?.hayCambiosSinPublicar && (
                <p className="text-sm text-texto-suave">Hay cambios guardados sin publicar todavía.</p>
            )}
            {consulta.data?.estaPublicada && consulta.data.publicadaEn && (
                <p className="text-sm text-texto-suave">Publicada por última vez el {consulta.data.publicadaEn}.</p>
            )}
            {mutacionPublicar.isError && <p className="text-sm text-red-600">Guarda la página antes de publicarla.</p>}

            <Tarjeta>
                <BuilderBloques slug={slug} bloques={bloques} onChange={setBloques} />
            </Tarjeta>

            {errorGeneral && <p className="text-sm text-red-600">{errorGeneral}</p>}

            <Boton onClick={() => mutacionGuardar.mutate()} disabled={mutacionGuardar.isPending}>
                {mutacionGuardar.isPending ? 'Guardando...' : 'Guardar bloques'}
            </Boton>
        </div>
    );
}
