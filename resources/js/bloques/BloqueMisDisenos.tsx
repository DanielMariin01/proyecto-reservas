import { Heart } from 'lucide-react';
import { useState } from 'react';
import type { MouseEvent, ReactElement } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { obtenerSesion } from '@/mi-cuenta/api/clienteCuenta';
import { alternarDisenoFavorito, obtenerIdsFavoritosDelNegocio } from '@/mi-cuenta/api/disenosFavoritos';
import { obtenerEstiloColor } from './colorLibre';
import type { PropiedadesBloque } from './tipos';

interface DisenoItem {
    id?: unknown;
    imagen?: unknown;
    titulo?: unknown;
    descripcion?: unknown;
}

interface Diseno {
    id: string | null;
    imagen: string;
    titulo: string;
    descripcion: string;
}

function leerDisenos(items: unknown): Diseno[] {
    if (!Array.isArray(items)) {
        return [];
    }

    return (items as DisenoItem[])
        .filter((item): item is DisenoItem & { imagen: string } => typeof item.imagen === 'string')
        .map((item) => ({
            id: typeof item.id === 'string' ? item.id : null,
            imagen: item.imagen,
            titulo: typeof item.titulo === 'string' ? item.titulo : '',
            descripcion: typeof item.descripcion === 'string' ? item.descripcion : '',
        }));
}

export default function BloqueMisDisenos({ datos, slug }: PropiedadesBloque): ReactElement {
    const [disenoAmpliado, setDisenoAmpliado] = useState<Diseno | null>(null);
    const disenos = leerDisenos(datos.items);
    const queryClient = useQueryClient();

    const sesionQuery = useQuery({ queryKey: ['sesion-cliente'], queryFn: obtenerSesion });
    const haySesion = Boolean(sesionQuery.data);

    const favoritosQuery = useQuery({
        queryKey: ['disenos-favoritos-negocio', slug],
        queryFn: () => obtenerIdsFavoritosDelNegocio(slug),
        enabled: haySesion,
    });

    const mutacionAlternar = useMutation({
        mutationFn: (disenoId: string) => alternarDisenoFavorito(slug, disenoId),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['disenos-favoritos-negocio', slug] }),
    });

    if (disenos.length === 0) {
        return <></>;
    }

    const idsFavoritos = favoritosQuery.data ?? [];

    function esFavorito(id: string | null): boolean {
        return id !== null && idsFavoritos.includes(id);
    }

    function alternar(id: string | null, evento?: MouseEvent): void {
        evento?.stopPropagation();

        if (id !== null) {
            mutacionAlternar.mutate(id);
        }
    }

    return (
        <section style={obtenerEstiloColor(datos)}>
            <div className="mx-auto max-w-[1200px] px-4 py-10 sm:py-16">
                <h2 className="mb-6 text-3xl font-bold sm:text-4xl">Mis diseños</h2>

                <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                    {disenos.map((diseno, indice) => (
                        <div key={diseno.id ?? indice} className="relative flex flex-col items-center gap-2 text-center">
                            <button type="button" onClick={() => setDisenoAmpliado(diseno)} className="flex w-full flex-col items-center gap-2">
                                <span className="aspect-square w-full overflow-hidden rounded-boton transition-opacity hover:opacity-90">
                                    <img src={diseno.imagen} alt="" className="h-full w-full object-cover" />
                                </span>
                                {diseno.titulo && <p className="font-semibold">{diseno.titulo}</p>}
                                {diseno.descripcion && <p className="line-clamp-2 text-sm opacity-80">{diseno.descripcion}</p>}
                            </button>
                            {haySesion && diseno.id !== null && (
                                <button
                                    type="button"
                                    aria-label={esFavorito(diseno.id) ? 'Quitar de favoritos' : 'Guardar en favoritos'}
                                    onClick={(evento) => alternar(diseno.id, evento)}
                                    className="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-blanco/90"
                                >
                                    <Heart size={16} strokeWidth={1.75} className={esFavorito(diseno.id) ? 'fill-primario text-primario' : 'text-tinta'} />
                                </button>
                            )}
                        </div>
                    ))}
                </div>

                {disenoAmpliado && (
                    <div
                        className="fixed inset-0 z-50 flex items-center justify-center bg-tinta/80 p-4"
                        onClick={() => setDisenoAmpliado(null)}
                        role="presentation"
                    >
                        <div className="relative max-h-[90vh] max-w-full overflow-hidden rounded-tarjeta bg-blanco" onClick={(evento) => evento.stopPropagation()}>
                            <img src={disenoAmpliado.imagen} alt="" className="max-h-[70vh] w-full object-contain" />
                            {haySesion && disenoAmpliado.id !== null && (
                                <button
                                    type="button"
                                    aria-label={esFavorito(disenoAmpliado.id) ? 'Quitar de favoritos' : 'Guardar en favoritos'}
                                    onClick={() => alternar(disenoAmpliado.id)}
                                    className="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-blanco/90"
                                >
                                    <Heart
                                        size={18}
                                        strokeWidth={1.75}
                                        className={esFavorito(disenoAmpliado.id) ? 'fill-primario text-primario' : 'text-tinta'}
                                    />
                                </button>
                            )}
                            {(disenoAmpliado.titulo || disenoAmpliado.descripcion) && (
                                <div className="p-4 text-center">
                                    {disenoAmpliado.titulo && <p className="font-semibold text-tinta">{disenoAmpliado.titulo}</p>}
                                    {disenoAmpliado.descripcion && <p className="mt-1 text-sm text-texto-suave">{disenoAmpliado.descripcion}</p>}
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}
