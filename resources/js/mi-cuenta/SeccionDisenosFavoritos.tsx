import { Heart } from 'lucide-react';
import type { ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import Boton from '@/componentes/Boton';
import { alternarDisenoFavorito, obtenerDisenosFavoritos } from './api/disenosFavoritos';

const CLAVE = ['mi-cuenta-disenos-favoritos'];

export default function SeccionDisenosFavoritos(): ReactElement {
    const queryClient = useQueryClient();
    const consulta = useQuery({ queryKey: CLAVE, queryFn: obtenerDisenosFavoritos });

    const mutacionQuitar = useMutation({
        mutationFn: ({ negocioSlug, disenoId }: { negocioSlug: string; disenoId: string }) => alternarDisenoFavorito(negocioSlug, disenoId),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: CLAVE }),
    });

    if (consulta.isLoading) {
        return (
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                {[0, 1, 2].map((indice) => (
                    <div key={indice} className="aspect-square animate-pulse rounded-tarjeta bg-gris-claro" />
                ))}
            </div>
        );
    }

    if (consulta.isError) {
        return (
            <div className="rounded-tarjeta bg-blanco p-6 text-center shadow-tarjeta">
                <p className="text-sm text-texto-suave">Algo falló cargando tus diseños favoritos. Código REF-DFV1.</p>
                <Boton variante="contorno" className="mt-4" onClick={() => consulta.refetch()}>
                    Reintentar
                </Boton>
            </div>
        );
    }

    const disenos = consulta.data ?? [];

    if (disenos.length === 0) {
        return (
            <p className="text-sm text-texto-suave">
                Todavía no tienes diseños favoritos. Marca el corazón en &ldquo;Mis diseños&rdquo; de la página de un negocio para guardarlo aquí.
            </p>
        );
    }

    return (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
            {disenos.map((diseno) => (
                <div key={`${diseno.negocioSlug}-${diseno.disenoId}`} className="space-y-1">
                    <div className="relative aspect-square overflow-hidden rounded-boton">
                        {diseno.imagen && <img src={diseno.imagen} alt="" className="h-full w-full object-cover" />}
                        <button
                            type="button"
                            aria-label="Quitar de favoritos"
                            disabled={mutacionQuitar.isPending}
                            onClick={() => mutacionQuitar.mutate({ negocioSlug: diseno.negocioSlug, disenoId: diseno.disenoId })}
                            className="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-blanco/90"
                        >
                            <Heart size={16} strokeWidth={1.75} className="fill-primario text-primario" />
                        </button>
                    </div>
                    {diseno.titulo && <p className="truncate text-sm font-semibold text-tinta">{diseno.titulo}</p>}
                    <p className="truncate text-xs text-texto-suave">{diseno.negocioNombre}</p>
                </div>
            ))}
        </div>
    );
}
